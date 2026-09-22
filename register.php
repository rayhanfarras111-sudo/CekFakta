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

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if (empty($errors)) {
        $check = db()->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role'     => 'user',
        ]);
        flash_set('flash_success', 'Registrasi berhasil! Silakan login.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Daftar';
require __DIR__ . '/includes/header.php';
?>

<section class="container">
    <div class="card form-narrow">
        <h1 style="text-align:center;">Buat akun CekFakta</h1>

        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

        <form method="POST" action="register.php" class="js-once">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar Akun</button>
        </form>

        <p style="text-align:center; margin-top:16px; font-size:0.9rem;">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
