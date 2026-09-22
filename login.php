<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Sesi form kedaluwarsa, silakan coba lagi.';
    }

    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $errors[] = 'Email/username dan password wajib diisi.';
    }

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :login OR username = :login LIMIT 1');
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
            exit;
        }
        $errors[] = 'Email atau password salah.';
    }
}

$pageTitle = 'Masuk';
require __DIR__ . '/includes/header.php';
?>

<section class="container">
    <div class="card form-narrow">
        <h1 style="text-align:center;">Masuk ke CekFakta</h1>

        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

        <form method="POST" action="login.php" class="js-once">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="login">Email atau username</label>
                <input type="text" name="login" id="login" required autocomplete="username" placeholder="contoh@email.com atau nama pengguna" value="<?= e($_POST['login'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <p style="text-align:center; margin-top:16px; font-size:0.9rem;">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
