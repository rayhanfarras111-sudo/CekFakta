<?php
require_once __DIR__ . '/db.php';

// ---------- Flash messages ----------
function flash_set(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!empty($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

// ---------- CSRF (proteksi form dari spam/serangan) ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// ---------- Teks & similarity ----------
function normalize_text(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/https?:\/\/\S+/', ' ', $text); // buang url saat normalisasi teks biasa
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

/**
 * Cari analisis lama yang mirip (cache) supaya tidak perlu panggil AI berulang
 * untuk klaim yang sama/mirip. Pakai pg_trgm similarity() di Postgres.
 * Return array analisis jika kemiripan >= $threshold, atau null jika tidak ada.
 */
function find_similar_analysis(string $normalizedText, float $threshold = 0.55): ?array
{
    if (trim($normalizedText) === '') {
        return null;
    }

    $stmt = db()->prepare(
        "SELECT *, similarity(normalized_text, :q) AS score
         FROM analyses
         WHERE similarity(normalized_text, :q) >= :threshold
         ORDER BY score DESC
         LIMIT 1"
    );
    $stmt->execute([':q' => $normalizedText, ':threshold' => $threshold]);
    $row = $stmt->fetch();

    return $row ?: null;
}

// ---------- Rate limiting ----------
/** Berapa kali user (atau IP untuk tamu) sudah melakukan analisis hari ini. */
function analyses_today_count(?int $userId): int
{
    if ($userId) {
        $stmt = db()->prepare(
            "SELECT COUNT(*) AS c FROM analyses WHERE user_id = :uid AND created_at::date = CURRENT_DATE"
        );
        $stmt->execute([':uid' => $userId]);
    } else {
        // Tamu tidak tersimpan per-IP di DB (privasi), jadi batasi lewat session saja.
        return (int) ($_SESSION['guest_analyses_today'][date('Y-m-d')] ?? 0);
    }
    return (int) $stmt->fetch()['c'];
}

function guest_hit_rate_limit(): void
{
    $today = date('Y-m-d');
    $_SESSION['guest_analyses_today'] = [$today => ($_SESSION['guest_analyses_today'][$today] ?? 0) + 1];
}

function is_rate_limited(?int $userId): bool
{
    return analyses_today_count($userId) >= MAX_ANALISIS_PER_HARI;
}

// ---------- Format tampilan ----------
function label_badge_class(string $label): string
{
    return match ($label) {
        'Kemungkinan Fakta'            => 'badge-fakta',
        'Kemungkinan Hoaks'            => 'badge-hoaks',
        'Perlu Konteks'                => 'badge-konteks',
        default                        => 'badge-unverified',
    };
}

function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}
