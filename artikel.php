<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT a.*, u.username FROM prebunking_articles a LEFT JOIN users u ON u.id = a.created_by WHERE a.id = :id');
$stmt->execute([':id' => $id]);
$article = $stmt->fetch();
if (!$article) {
    http_response_code(404);
    $pageTitle = 'Artikel tidak ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<section class="container"><div class="empty-state"><h2>Artikel tidak ditemukan</h2><p><a href="cek-berita.php">Kembali ke Cek Berita</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $article['title'];
require __DIR__ . '/includes/header.php';
?>
<section class="container article-detail" style="padding-top:32px;">
    <a href="cek-berita.php">&larr; Kembali ke Cek Berita</a>
    <article class="article-detail-card">
        <?php if ($article['image_url']): ?><img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>"><?php endif; ?>
        <div class="article-detail-body">
            <span class="article-cat"><?= e($article['category']) ?> · <?= e($article['verdict']) ?></span>
            <h1><?= e($article['title']) ?></h1>
            <p class="article-detail-date"><?= e(date('d M Y', strtotime($article['published_at']))) ?><?= $article['username'] ? ' · oleh ' . e($article['username']) : '' ?></p>
            <p class="article-lead"><?= e($article['summary']) ?></p>
            <div class="article-content"><?= nl2br(e($article['body'])) ?></div>
            <?php if ($article['source_url']): ?><a class="btn btn-primary" href="<?= e($article['source_url']) ?>" target="_blank" rel="noopener noreferrer">Buka sumber rujukan &rarr;</a><?php endif; ?>
        </div>
    </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
