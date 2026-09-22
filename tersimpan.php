<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$stmt = db()->prepare(
    'SELECT a.*, b.created_at AS saved_at FROM bookmarks b INNER JOIN analyses a ON a.id = b.analysis_id WHERE b.user_id = :uid ORDER BY b.created_at DESC'
);
$stmt->execute([':uid' => current_user()['id']]);
$bookmarks = $stmt->fetchAll();

$pageTitle = 'Tersimpan';
require __DIR__ . '/includes/header.php';
?>
<section class="container" style="padding-top:32px;">
    <h1>Tersimpan</h1>
    <p class="hero-sub">Hasil analisis yang kamu simpan untuk dibaca kembali.</p>
    <?php if (empty($bookmarks)): ?>
        <div class="empty-state"><h3>Belum ada yang disimpan</h3><p><a href="cek-berita.php">Cek berita</a> lalu simpan hasil yang penting.</p></div>
    <?php else: ?>
        <?php foreach ($bookmarks as $bookmark): ?>
            <a href="hasil.php?id=<?= (int) $bookmark['id'] ?>" class="card history-item" style="text-decoration:none; color:inherit; display:flex;">
                <div><span class="badge <?= label_badge_class($bookmark['label']) ?>"><?= e($bookmark['label']) ?></span><p class="snippet"><?= e(mb_substr($bookmark['input_content'], 0, 140)) ?><?= mb_strlen($bookmark['input_content']) > 140 ? '...' : '' ?></p></div>
                <div style="text-align:right; flex:none;"><strong><?= (int) $bookmark['credibility_score'] ?></strong><div class="dashboard-list-meta">Disimpan <?= time_ago($bookmark['saved_at']) ?></div></div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>