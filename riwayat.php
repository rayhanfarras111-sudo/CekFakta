<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user()['id'];

$label     = trim($_GET['label'] ?? '');
$startDate = trim($_GET['start'] ?? '');
$endDate   = trim($_GET['end'] ?? '');
$q         = trim($_GET['q'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$perPage   = 10;

$where  = ['user_id = :uid'];
$params = [':uid' => $userId];

if ($label !== '') {
    $where[] = 'label = :label';
    $params[':label'] = $label;
}
if ($startDate !== '') {
    $where[] = 'created_at >= :start';
    $params[':start'] = $startDate . ' 00:00:00';
}
if ($endDate !== '') {
    $where[] = 'created_at <= :end';
    $params[':end'] = $endDate . ' 23:59:59';
}
$where[] = $q !== '' ? '(input_content ILIKE :q OR explanation ILIKE :q)' : '1 = 1';
if ($q !== '') {
    $params[':q'] = '%' . $q . '%';
}
$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM analyses WHERE {$whereSql}");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT * FROM analyses WHERE {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute($params);
$history = $stmt->fetchAll();

$pageTitle = 'Riwayat Pribadi';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <h1>Riwayat Pribadi</h1>
    <p class="hero-sub">Semua klaim yang pernah kamu periksa lewat CekFakta.</p>

    <form method="GET" action="riwayat.php" class="filter-bar">
        <select name="label" onchange="this.form.submit()">
            <option value="">Semua label</option>
            <?php foreach (['Kemungkinan Fakta', 'Kemungkinan Hoaks', 'Perlu Konteks', 'Belum Dapat Diverifikasi'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $label === $l ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="start" value="<?= e($startDate) ?>" onchange="this.form.submit()">
        <input type="date" name="end" value="<?= e($endDate) ?>" onchange="this.form.submit()">
        <input type="search" name="q" placeholder="Cari klaim..." value="<?= e($q) ?>">
        <button type="submit" class="btn btn-ghost">Terapkan</button>
    </form>

    <?php if (empty($history)): ?>
        <div class="empty-state">
            <h3>Belum ada riwayat</h3>
            <p><a href="cek-berita.php">Mulai cek berita pertamamu</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($history as $h): ?>
            <a href="hasil.php?id=<?= (int) $h['id'] ?>" class="card history-item" style="text-decoration:none; color:inherit; display:flex;">
                <div>
                    <span class="badge <?= label_badge_class($h['label']) ?>"><?= e($h['label']) ?></span>
                    <p class="snippet"><?= e(mb_substr($h['input_content'], 0, 120)) ?><?= mb_strlen($h['input_content']) > 120 ? '…' : '' ?></p>
                </div>
                <div style="text-align:right; flex:none;">
                    <div style="font-family:'Poppins',sans-serif; font-weight:700; color:var(--merah-tua);"><?= (int) $h['credibility_score'] ?></div>
                    <div style="font-size:0.78rem; color:var(--ink-soft);"><?= time_ago($h['created_at']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Halaman riwayat">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a class="<?= $p === $page ? 'active' : '' ?>" href="?label=<?= urlencode($label) ?>&start=<?= urlencode($startDate) ?>&end=<?= urlencode($endDate) ?>&q=<?= urlencode($q) ?>&page=<?= $p ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
