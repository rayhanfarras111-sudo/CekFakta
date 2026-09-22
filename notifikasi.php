<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $stmt = db()->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = :uid');
    $stmt->execute([':uid' => $userId]);
    flash_set('flash_success', 'Semua notifikasi ditandai sudah dibaca.');
    header('Location: notifikasi.php');
    exit;
}
$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50');
$stmt->execute([':uid' => $userId]);
$notifications = $stmt->fetchAll();
$pageTitle = 'Notifikasi';
require __DIR__ . '/includes/header.php';
?>
<section class="container" style="padding-top:32px; max-width:760px;">
    <div class="section-heading"><div><p class="hero-eyebrow">Aktivitas akun</p><h1>Notifikasi</h1></div><?php if (!empty($notifications)): ?><form method="POST" action="notifikasi.php"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit">Tandai sudah dibaca</button></form><?php endif; ?></div>
    <?php if (empty($notifications)): ?><div class="empty-state"><h3>Belum ada notifikasi</h3><p>Aktivitas upvote dan balasan akan muncul di sini.</p></div><?php else: ?>
        <div class="notification-list">
        <?php foreach ($notifications as $notification): ?><a href="<?= e($notification['link'] ?: '#') ?>" class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>"><span class="notification-dot"></span><div><p><?= e($notification['message']) ?></p><span class="dashboard-list-meta"><?= time_ago($notification['created_at']) ?></span></div></a><?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
