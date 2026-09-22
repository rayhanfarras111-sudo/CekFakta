<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$rootPath = str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : '';
$isAdminArea = str_contains($_SERVER['SCRIPT_NAME'], '/admin/');
$unreadNotifications = 0;
if (is_logged_in()) {
    $notificationCount = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = FALSE');
    $notificationCount->execute([':uid' => current_user()['id']]);
    $unreadNotifications = (int) $notificationCount->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — CekFakta' : 'CekFakta — Deteksi Berita Hoaks Berbasis AI' ?></title>
<meta name="description" content="CekFakta membantu kamu memeriksa kebenaran berita dengan AI, diskusi komunitas, dan panduan literasi media.">
<link rel="icon" type="image/svg+xml" href="<?= str_repeat('../', substr_count($_SERVER['SCRIPT_NAME'], '/admin/')) ?>assets/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['SCRIPT_NAME'], '/admin/')) ?>assets/css/style.css">
</head>
<body>
<header class="site-nav">
    <div class="nav-inner">
        <a href="<?= str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../index.php' : 'index.php' ?>" class="brand">
            <span class="brand-mark">CF</span> CekFakta
        </a>

        <?php if (!$isAdminArea): ?>
        <nav class="nav-links">
            <a href="cek-berita.php" class="<?= $currentPage === 'cek-berita.php' ? 'active' : '' ?>">Cek Berita</a>
            <a href="komunitas.php" class="<?= in_array($currentPage, ['komunitas.php', 'diskusi.php']) ? 'active' : '' ?>">Komunitas</a>
            <a href="literasi.php" class="<?= $currentPage === 'literasi.php' ? 'active' : '' ?>">Literasi</a>
            <?php if (is_logged_in()): ?>
                <a href="riwayat.php" class="<?= $currentPage === 'riwayat.php' ? 'active' : '' ?>">Riwayat</a>
                <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <a href="tersimpan.php" class="<?= $currentPage === 'tersimpan.php' ? 'active' : '' ?>">Tersimpan</a>
            <?php endif; ?>
        </nav>
        <?php else: ?>
        <nav class="nav-links admin-nav-links">
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="dashboard.php#moderasi">Moderasi</a>
            <a href="artikel.php" class="<?= $currentPage === 'artikel.php' ? 'active' : '' ?>">Artikel</a>
        </nav>
        <?php endif; ?>

        <div class="nav-auth">
            <?php if (is_logged_in()): ?>
                <?php if (is_admin() && !$isAdminArea): ?><a href="admin/dashboard.php" class="nav-admin-link">Dashboard Admin</a><?php endif; ?>
                <span class="nav-username">Hai, <?= e(current_user()['username']) ?></span>
                <a href="<?= $rootPath ?>profil.php" class="btn btn-ghost btn-sm">Profil</a>
                <a href="<?= $rootPath ?>notifikasi.php" class="btn btn-ghost btn-sm" aria-label="Notifikasi">&#128276;<?= $unreadNotifications ? ' ' . $unreadNotifications : '' ?></a>
                <a href="<?= str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../logout.php' : 'logout.php' ?>" class="btn btn-ghost">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-ghost">Masuk</a>
                <a href="register.php" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
        </div>

        <button class="nav-burger" aria-label="Buka menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">&#9776;</button>
    </div>
</header>
<main>
<?php
$flashSuccess = flash_get('flash_success');
$flashError   = flash_get('flash_error');
if ($flashSuccess): ?>
    <div class="container"><div class="alert alert-success"><?= e($flashSuccess) ?></div></div>
<?php endif;
if ($flashError): ?>
    <div class="container"><div class="alert alert-error"><?= e($flashError) ?></div></div>
<?php endif; ?>
