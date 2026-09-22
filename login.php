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
    <div class="auth-layout">
        <aside class="auth-intro">
            <span class="auth-mark">CF</span>
            <span class="auth-geometry" aria-hidden="true"></span>
            <p class="auth-kicker">RUANG CEK FAKTA</p>
            <h1>Selamat datang kembali.</h1>
            <p class="auth-intro-text">Lanjutkan kebiasaan baik untuk berhenti sejenak, mengecek informasi, dan berbagi dengan lebih bijak.</p>
            <div class="auth-points">
                <span><b>01</b> Periksa klaim dengan AI</span>
                <span><b>02</b> Simpan riwayat analisis</span>
                <span><b>03</b> Diskusi bersama komunitas</span>
            </div>
            <div class="auth-signature"><span></span> CHECK · THINK · SHARE</div>
        </aside>

        <div class="card auth-card">
            <div class="auth-card-heading">
                <h2>Masuk ke CekFakta</h2>
                <p>Gunakan email atau username untuk melanjutkan.</p>
            </div>

            <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

            <form method="POST" action="login.php" class="js-once">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="login">Email atau username</label>
                    <div class="input-with-icon">
                        <span class="input-icon" aria-hidden="true">@</span>
                        <input type="text" name="login" id="login" required autocomplete="username" placeholder="contoh@email.com atau nama pengguna" value="<?= e($_POST['login'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon input-password">
                        <span class="input-icon" aria-hidden="true">*</span>
                        <input type="password" name="password" id="password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Tampilkan password">Lihat</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk <span aria-hidden="true">→</span></button>
                <p class="auth-security"><span aria-hidden="true">✓</span> Data kamu tetap aman dan terlindungi.</p>
            </form>

            <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
