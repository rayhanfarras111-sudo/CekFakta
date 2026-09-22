<?php
/**
 * config.php
 * Memuat variabel dari file .env (TIDAK boleh di-commit ke Git) dan
 * menyediakannya sebagai konstanta untuk seluruh aplikasi.
 *
 * Kenapa begini? Sebelumnya kredensial Supabase & API key Gemini
 * ditulis langsung di dalam kode (koneksi.php / index.php). Ini bahaya:
 * siapa pun yang melihat source code (atau repo Git) langsung dapat
 * password database & API key kamu. Solusinya: taruh semua rahasia di
 * file `.env` yang di-gitignore, lalu load lewat file ini.
 */

function load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // buang tanda kutip pembungkus jika ada
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

load_env(__DIR__ . '/.env');

function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

// ==== Database (Supabase Postgres) ====
define('DB_HOST', env('DB_HOST', ''));
define('DB_PORT', env('DB_PORT', '6543'));
define('DB_NAME', env('DB_NAME', 'postgres'));
define('DB_USER', env('DB_USER', ''));
define('DB_PASS', env('DB_PASS', ''));

// ==== Gemini AI ====
define('GEMINI_API_KEY', env('GEMINI_API_KEY', ''));
define('GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-2.5-flash'));

// ==== Aplikasi ====
define('APP_NAME', 'CekFakta');
define('APP_URL', env('APP_URL', 'http://localhost/ProjectAkhirSintech'));
define('MAX_ANALISIS_PER_HARI', (int) env('MAX_ANALISIS_PER_HARI', 15)); // rate limit per user/hari
define('UPLOAD_DIR', __DIR__ . '/uploads/screenshots/');
define('UPLOAD_URL', 'uploads/screenshots/');

date_default_timezone_set('Asia/Jakarta');
