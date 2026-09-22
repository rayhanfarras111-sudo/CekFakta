<?php
require_once __DIR__ . '/../config.php';

/**
 * Mengembalikan koneksi PDO ke Supabase (singleton per-request).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    if (DB_HOST === '' || DB_USER === '') {
        die('Konfigurasi database belum diisi. Salin .env.example menjadi .env lalu isi kredensial Supabase kamu.');
    }

    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('Koneksi Supabase gagal: ' . $e->getMessage());
        die('Tidak bisa terhubung ke database. Coba lagi beberapa saat lagi.');
    }

    return $pdo;
}
