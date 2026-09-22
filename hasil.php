<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookmark'])) {
    require_login();
    if (csrf_verify()) {
        $bookmark = db()->prepare(
            'INSERT INTO bookmarks (user_id, analysis_id) VALUES (:uid, :aid) ON CONFLICT (user_id, analysis_id) DO NOTHING'
        );
        $bookmark->execute([':uid' => current_user()['id'], ':aid' => $id]);
        flash_set('flash_success', 'Hasil analisis disimpan ke bookmark.');
    }
    header('Location: hasil.php?id=' . $id);
    exit;
}

$stmt = db()->prepare("SELECT a.*, u.username FROM analyses a LEFT JOIN users u ON u.id = a.user_id WHERE a.id = :id");
$stmt->execute([':id' => $id]);
$analysis = $stmt->fetch();

if (!$analysis) {
    http_response_code(404);
    $pageTitle = 'Tidak Ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="empty-state"><h2>Hasil analisis tidak ditemukan</h2><p><a href="cek-berita.php">Coba cek berita baru</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$sources = json_decode($analysis['sources'] ?? '[]', true) ?: [];
$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/hasil.php?id=' . (int) $analysis['id'];
$score = (int) $analysis['credibility_score'];
$scoreNote = $score >= 75 ? 'Indikasi kredibilitas tinggi' : ($score >= 50 ? 'Perlu dibandingkan dengan sumber lain' : 'Perlu verifikasi ekstra');
$saved = false;
if (is_logged_in()) {
    $savedStmt = db()->prepare('SELECT 1 FROM bookmarks WHERE user_id = :uid AND analysis_id = :aid');
    $savedStmt->execute([':uid' => current_user()['id'], ':aid' => $id]);
    $saved = (bool) $savedStmt->fetchColumn();
}
$pageTitle = 'Hasil Analisis';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px; max-width:760px;">
    <div class="card">
        <p class="hero-eyebrow">Kartu klaim yang dianalisis</p>
        <p style="font-size:1.05rem; color:var(--ink);"><?= nl2br(e($analysis['input_content'])) ?></p>
        <?php if ($analysis['image_path']): ?>
            <img src="<?= e($analysis['image_path']) ?>" alt="Screenshot yang dianalisis" style="border-radius:var(--radius-s); margin-top:10px; max-height:320px; object-fit:cover;">
        <?php endif; ?>

        <div class="result-header" style="margin-top:20px;">
            <div class="score-ring" style="--score: <?= (int) $analysis['credibility_score'] ?>;" data-score="<?= (int) $analysis['credibility_score'] ?>"></div>
            <div>
                <span class="badge <?= label_badge_class($analysis['label']) ?>"><?= e($analysis['label']) ?></span>
                <p style="margin:6px 0 0; color:var(--ink-soft); font-size:0.85rem;">Dianalisis <?= time_ago($analysis['created_at']) ?><?= $analysis['username'] ? ' oleh ' . e($analysis['username']) : '' ?></p>
                <p class="score-note"><?= e($scoreNote) ?> · <?= e(strtoupper($analysis['input_type'])) ?> · <?= count($sources) ?> sumber</p>
            </div>
        </div>

        <h3 style="margin-top:22px;">Penjelasan</h3>
        <p><?= nl2br(e($analysis['explanation'])) ?></p>

        <?php if (!empty($sources)): ?>
            <h3>Sumber rujukan</h3>
            <ul class="result-sources">
                <?php foreach ($sources as $src): ?>
                    <li>
                        <a href="<?= e($src['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer nofollow">
                            <?= e($src['title'] ?? $src['url'] ?? 'Sumber') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="disclaimer">Hasil ini bantuan analisis AI, bukan keputusan final. Selalu periksa ke sumber resmi sebelum mengambil kesimpulan.</div>

        <div class="check-form-row">
            <a href="komunitas.php?dari_analisis=<?= (int) $analysis['id'] ?>" class="btn btn-primary">Diskusikan di komunitas</a>
            <?php if (is_logged_in()): ?>
                <?php if ($saved): ?>
                    <a href="tersimpan.php" class="btn btn-ghost">&#10003; Tersimpan</a>
                <?php else: ?>
                    <form method="POST" action="hasil.php?id=<?= (int) $analysis['id'] ?>" style="display:inline-block;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="bookmark" value="1">
                        <button type="submit" class="btn btn-ghost">&#9733; Simpan</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <button type="button" class="btn btn-ghost" data-copy-url="<?= e('hasil.php?id=' . (int) $analysis['id']) ?>">Salin link</button>
            <a class="btn btn-ghost" target="_blank" rel="noopener" href="https://wa.me/?text=<?= rawurlencode('Hasil cek CekFakta: ' . $analysis['label'] . ' - ' . $shareUrl) ?>">Bagikan WhatsApp</a>
            <?php if (is_logged_in() && $analysis['user_id'] == current_user()['id']): ?>
                <span class="btn btn-ghost" style="cursor:default;">&#10003; Tersimpan di riwayat kamu</span>
            <?php elseif (!is_logged_in()): ?>
                <a href="login.php" class="btn btn-ghost">Login untuk simpan ke riwayat</a>
            <?php endif; ?>
            <a href="cek-berita.php" class="btn btn-ghost">Cek berita lain</a>
        </div>
    </div>

    <?php if (is_logged_in()): ?>
    <details class="card" style="margin-top:16px;">
        <summary style="cursor:pointer; font-weight:600; color:var(--merah-tua);">Rasa hasil ini kurang tepat? Laporkan</summary>
        <form action="report.php" method="POST" class="js-once" style="margin-top:12px;">
            <?= csrf_field() ?>
            <input type="hidden" name="analysis_id" value="<?= (int) $analysis['id'] ?>">
            <div class="form-group">
                <label for="reason">Ceritakan alasannya</label>
                <textarea name="reason" id="reason" rows="3" required placeholder="Contoh: sumbernya tidak relevan, skor terasa keliru, dst."></textarea>
            </div>
            <button type="submit" class="btn btn-ghost">Kirim laporan</button>
        </form>
    </details>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
