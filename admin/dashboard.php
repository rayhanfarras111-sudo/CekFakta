<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$totalAnalyses  = db()->query("SELECT COUNT(*) AS c FROM analyses")->fetch()['c'];
$totalToday     = db()->query("SELECT COUNT(*) AS c FROM analyses WHERE created_at::date = CURRENT_DATE")->fetch()['c'];
$totalUsers     = db()->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$totalTopics    = db()->query("SELECT COUNT(*) AS c FROM discussion_topics WHERE is_hidden = FALSE")->fetch()['c'];

$trending = db()->query(
    "SELECT t.id, t.title, COUNT(c.id) AS jumlah_komentar
     FROM discussion_topics t LEFT JOIN comments c ON c.topic_id = t.id AND c.is_hidden = FALSE
     WHERE t.is_hidden = FALSE
     GROUP BY t.id ORDER BY jumlah_komentar DESC, t.created_at DESC LIMIT 5"
)->fetchAll();

$pendingReports = db()->query(
    "SELECT r.*, a.input_content, a.label, u.username AS reporter
     FROM reports r
     JOIN analyses a ON a.id = r.analysis_id
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.status = 'pending'
     ORDER BY r.created_at DESC LIMIT 20"
)->fetchAll();

$recentTopics = db()->query(
    "SELECT t.id, t.title, t.is_hidden, u.username, t.created_at
     FROM discussion_topics t LEFT JOIN users u ON u.id = t.user_id
     ORDER BY t.created_at DESC LIMIT 15"
)->fetchAll();

$pageTitle = 'Dashboard Admin';
require __DIR__ . '/../includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <div class="admin-hero">
        <div>
            <p class="admin-kicker">CONTROL ROOM · CEKFAKTA</p>
            <h1>Dashboard Admin</h1>
            <p>Kelola kualitas analisis, pantau aktivitas komunitas, dan jaga percakapan tetap sehat.</p>
        </div>
        <div class="admin-badge"><span>CF</span><div><strong>ADMIN</strong><small><?= e(current_user()['username']) ?></small></div></div>
    </div>

    <div class="admin-actions">
        <a href="../komunitas.php" target="_blank" class="btn btn-ghost">Lihat komunitas &rarr;</a>
        <a href="#moderasi" class="btn btn-admin">Panel moderasi</a>
        <a href="artikel.php" class="btn btn-ghost">Kelola artikel</a>
    </div>

    <div class="stat-grid admin-stats">
        <div class="stat-card"><div class="num"><?= (int) $totalAnalyses ?></div><div class="label">Total analisis AI</div></div>
        <div class="stat-card"><div class="num"><?= (int) $totalToday ?></div><div class="label">Analisis hari ini</div></div>
        <div class="stat-card"><div class="num"><?= (int) $totalUsers ?></div><div class="label">Total pengguna</div></div>
        <div class="stat-card"><div class="num"><?= (int) $totalTopics ?></div><div class="label">Topik diskusi aktif</div></div>
    </div>

    <div class="card">
        <h3>Topik trending (berdasarkan jumlah komentar)</h3>
        <?php if (empty($trending)): ?>
            <p style="color:var(--ink-soft);">Belum ada aktivitas diskusi.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Judul</th><th>Komentar</th></tr></thead>
                <tbody>
                <?php foreach ($trending as $t): ?>
                    <tr><td><a href="../diskusi.php?id=<?= (int) $t['id'] ?>" target="_blank"><?= e($t['title']) ?></a></td><td><?= (int) $t['jumlah_komentar'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Laporan hasil analisis (menunggu tinjauan)</h3>
        <?php if (empty($pendingReports)): ?>
            <p style="color:var(--ink-soft);">Tidak ada laporan yang menunggu.</p>
        <?php else: ?>
            <?php foreach ($pendingReports as $r): ?>
                <div class="comment">
                    <p style="margin:0 0 4px;"><strong><?= e($r['reporter'] ?? 'Pengguna') ?></strong> melaporkan <a href="../hasil.php?id=<?= (int) $r['analysis_id'] ?>" target="_blank">analisis #<?= (int) $r['analysis_id'] ?></a> (<?= e($r['label']) ?>)</p>
                    <p style="color:var(--ink-soft); margin:0 0 8px;">"<?= e($r['reason']) ?>"</p>
                    <form method="POST" action="moderasi.php" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="resolve_report">
                        <input type="hidden" name="report_id" value="<?= (int) $r['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-ghost">Tandai selesai</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" id="moderasi">
        <h3>Moderasi topik terbaru</h3>
        <table>
            <thead><tr><th>Judul</th><th>Penulis</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($recentTopics as $t): ?>
                <tr>
                    <td><a href="../diskusi.php?id=<?= (int) $t['id'] ?>" target="_blank"><?= e($t['title']) ?></a></td>
                    <td><?= e($t['username'] ?? 'Pengguna') ?></td>
                    <td><?= $t['is_hidden'] ? 'Disembunyikan' : 'Tampil' ?></td>
                    <td>
                        <form method="POST" action="moderasi.php" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="<?= $t['is_hidden'] ? 'unhide_topic' : 'hide_topic' ?>">
                            <input type="hidden" name="topic_id" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-ghost"><?= $t['is_hidden'] ? 'Tampilkan' : 'Sembunyikan' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
