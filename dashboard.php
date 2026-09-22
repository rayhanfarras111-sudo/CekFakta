<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user()['id'];

$summaryStmt = db()->prepare(
    "SELECT COUNT(*) AS total_analyses,
            COALESCE(ROUND(AVG(credibility_score)), 0) AS average_score
     FROM analyses WHERE user_id = :uid"
);
$summaryStmt->execute([':uid' => $userId]);
$summary = $summaryStmt->fetch();

$topicStmt = db()->prepare('SELECT COUNT(*) FROM discussion_topics WHERE user_id = :uid');
$topicStmt->execute([':uid' => $userId]);
$topicCount = (int) $topicStmt->fetchColumn();

$voteStmt = db()->prepare(
    "SELECT COUNT(*)
     FROM discussion_votes v
     INNER JOIN discussion_topics t ON t.id = v.topic_id
     WHERE t.user_id = :uid"
);
$voteStmt->execute([':uid' => $userId]);
$receivedVotes = (int) $voteStmt->fetchColumn();

$labelStmt = db()->prepare(
    'SELECT label, COUNT(*) AS total FROM analyses WHERE user_id = :uid GROUP BY label ORDER BY total DESC'
);
$labelStmt->execute([':uid' => $userId]);
$labelCounts = $labelStmt->fetchAll();
$maxLabelCount = 1;
foreach ($labelCounts as $labelCount) {
    $maxLabelCount = max($maxLabelCount, (int) $labelCount['total']);
}

$recentStmt = db()->prepare(
    'SELECT id, input_content, label, credibility_score, created_at FROM analyses WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5'
);
$recentStmt->execute([':uid' => $userId]);
$recentAnalyses = $recentStmt->fetchAll();

$pageTitle = 'Dashboard Saya';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <div class="dashboard-heading">
        <div>
            <p class="hero-eyebrow">Ruang pribadi</p>
            <h1>Dashboard Saya</h1>
            <p class="hero-sub">Pantau perjalananmu dalam memeriksa informasi dan ikut menjaga ruang diskusi tetap sehat.</p>
        </div>
        <a href="cek-berita.php" class="btn btn-primary">+ Cek berita baru</a>
    </div>

    <div class="stat-grid dashboard-stats">
        <div class="stat-card"><div class="num"><?= (int) $summary['total_analyses'] ?></div><div class="label">Berita dicek</div></div>
        <div class="stat-card"><div class="num"><?= (int) $summary['average_score'] ?></div><div class="label">Skor rata-rata</div></div>
        <div class="stat-card"><div class="num"><?= $topicCount ?></div><div class="label">Topik dibuat</div></div>
        <div class="stat-card"><div class="num"><?= $receivedVotes ?></div><div class="label">Upvote diterima</div></div>
    </div>

    <div class="dashboard-grid">
        <section class="card">
            <div class="section-heading">
                <div><p class="hero-eyebrow">Ringkasan</p><h2>Distribusi hasil analisis</h2></div>
                <a href="riwayat.php" class="btn btn-ghost btn-sm">Lihat riwayat</a>
            </div>
            <?php if (empty($labelCounts)): ?>
                <div class="empty-state compact"><p>Belum ada hasil analisis untuk ditampilkan.</p><a href="cek-berita.php">Mulai cek berita</a></div>
            <?php else: ?>
                <div class="label-chart" aria-label="Grafik distribusi hasil analisis">
                    <?php foreach ($labelCounts as $item): ?>
                        <?php $width = max(4, (int) round(((int) $item['total'] / $maxLabelCount) * 100)); ?>
                        <div class="chart-row">
                            <div class="chart-label"><span><?= e($item['label']) ?></span><strong><?= (int) $item['total'] ?></strong></div>
                            <div class="chart-track"><span class="chart-bar <?= label_badge_class($item['label']) ?>" style="width:<?= $width ?>%;"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="section-heading"><div><p class="hero-eyebrow">Aktivitas</p><h2>Cek terbaru</h2></div></div>
            <?php if (empty($recentAnalyses)): ?>
                <div class="empty-state compact"><p>Aktivitasmu akan muncul di sini.</p></div>
            <?php else: ?>
                <div class="dashboard-list">
                    <?php foreach ($recentAnalyses as $analysis): ?>
                        <a href="hasil.php?id=<?= (int) $analysis['id'] ?>" class="dashboard-list-item">
                            <span class="badge <?= label_badge_class($analysis['label']) ?>"><?= e($analysis['label']) ?></span>
                            <span class="dashboard-list-title"><?= e(mb_substr($analysis['input_content'], 0, 72)) ?><?= mb_strlen($analysis['input_content']) > 72 ? '...' : '' ?></span>
                            <span class="dashboard-list-meta">Skor <?= (int) $analysis['credibility_score'] ?> · <?= time_ago($analysis['created_at']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
