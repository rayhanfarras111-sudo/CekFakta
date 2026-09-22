<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user()['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Sesi form kedaluwarsa, coba lagi.';
    }
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || mb_strlen($username) > 100) {
        $errors[] = 'Username wajib diisi dan maksimal 100 karakter.';
    }
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password baru minimal 8 karakter.';
    }
    if (empty($errors)) {
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE users SET username = :username, password = :password WHERE id = :id');
            $stmt->execute([':username' => $username, ':password' => password_hash($password, PASSWORD_DEFAULT), ':id' => $userId]);
        } else {
            $stmt = db()->prepare('UPDATE users SET username = :username WHERE id = :id');
            $stmt->execute([':username' => $username, ':id' => $userId]);
        }
        $_SESSION['user']['username'] = $username;
        flash_set('flash_success', 'Profil berhasil diperbarui.');
        header('Location: profil.php');
        exit;
    }
}

$userStmt = db()->prepare('SELECT username, email, created_at FROM users WHERE id = :id');
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch();
$pageTitle = 'Profil Saya';
require __DIR__ . '/includes/header.php';
?>
<section class="container" style="padding-top:32px; max-width:680px;">
    <p class="hero-eyebrow">Pengaturan akun</p>
    <h1>Profil Saya</h1>
    <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
    <div class="card">
        <div class="profile-hero"><span class="avatar profile-avatar"><?= e(strtoupper(mb_substr($user['username'], 0, 1))) ?></span><div><h2><?= e($user['username']) ?></h2><p class="dashboard-list-meta">Bergabung <?= time_ago($user['created_at']) ?></p></div></div>
        <form method="POST" action="profil.php" class="js-once">
            <?= csrf_field() ?>
            <div class="form-group"><label for="username">Nama pengguna</label><input type="text" id="username" name="username" maxlength="100" required value="<?= e($user['username']) ?>"></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" disabled value="<?= e($user['email']) ?>"></div>
            <div class="form-group"><label for="password">Password baru <span class="dashboard-list-meta">(opsional)</span></label><input type="password" id="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengganti"></div>
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
