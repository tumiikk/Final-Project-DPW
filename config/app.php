<?php

/**
 * Konfigurasi utama aplikasi.
 *
 * Semua nilai rahasia (password database, password admin) dibaca dari
 * Environment Variables / file .env, TIDAK ditulis langsung di file ini.
 * - Lokal (Laragon)  : isi file .env (salin dari .env.example).
 * - Railway          : isi tab "Variables" pada service web.
 */

/**
 * Menyusun konfigurasi database dengan urutan prioritas:
 * 1. DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD (gaya Laragon/.env)
 * 2. MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD (variabel bawaan Railway MySQL)
 * 3. MYSQL_URL / DATABASE_URL berbentuk mysql://user:pass@host:port/nama_db
 */
$database = (function (): array {
    $db = [
        'host' => env('DB_HOST') ?? env('MYSQLHOST'),
        'port' => env('DB_PORT') ?? env('MYSQLPORT'),
        'database' => env('DB_DATABASE') ?? env('MYSQLDATABASE') ?? env('MYSQL_DATABASE'),
        'username' => env('DB_USERNAME') ?? env('MYSQLUSER'),
        'password' => env('DB_PASSWORD') ?? env('MYSQLPASSWORD') ?? env('MYSQL_ROOT_PASSWORD'),
    ];

    // Jika variabel terpisah tidak ada, coba ambil dari URL koneksi.
    $url = env('DB_URL') ?? env('MYSQL_URL') ?? env('DATABASE_URL');
    if ($url && ($parts = parse_url($url)) !== false) {
        $db['host'] ??= $parts['host'] ?? null;
        $db['port'] ??= $parts['port'] ?? null;
        $db['database'] ??= isset($parts['path']) ? ltrim($parts['path'], '/') : null;
        $db['username'] ??= isset($parts['user']) ? urldecode($parts['user']) : null;
        $db['password'] ??= isset($parts['pass']) ? urldecode($parts['pass']) : null;
    }

    return [
        'host' => $db['host'] ?? '127.0.0.1',
        'port' => (int) ($db['port'] ?? 3306),
        'database' => $db['database'] ?? 'wedding_invitation',
        'username' => $db['username'] ?? 'root',
        'password' => $db['password'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];
})();

return [
    'name' => env('APP_NAME', 'Undangan Ayu & Kevin'),

    // local | production
    'env' => env('APP_ENV', 'production'),

    // true = tampilkan detail error (hanya untuk lokal). Di production WAJIB false.
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),

    // URL publik website, dipakai untuk link undangan personal tamu.
    // Kosongkan agar dideteksi otomatis dari request.
    'url' => rtrim((string) env('APP_URL', ''), '/'),

    // Zona waktu penyimpanan waktu di database (sama dengan project Laravel asli: UTC).
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'database' => $database,

    'session' => [
        'cookie' => env('SESSION_COOKIE', 'undangan_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 120), // menit
        // true = cookie hanya dikirim lewat HTTPS. Kosong = otomatis (aktif bila request HTTPS).
        'secure' => env('SESSION_SECURE_COOKIE'),
    ],
];
