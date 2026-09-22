<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

/** Redirect ke login kalau belum login. Panggil di paling atas halaman yang butuh login. */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Silakan login terlebih dahulu untuk mengakses halaman ini.';
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Akses ditolak. Halaman ini khusus admin.');
    }
}

function base_url(string $path = ''): string
{
    // Halaman admin berada satu folder lebih dalam dari halaman utama.
    return str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' . ltrim($path, '/') : $path;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
        'role'     => $user['role'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}
