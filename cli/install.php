<?php

/**
 * INSTALASI / SEEDER (pengganti `php artisan migrate` + `php artisan db:seed`).
 *
 * Jalankan dari folder proyek:   php cli/install.php
 *
 * Langkah yang dilakukan (semuanya aman dijalankan berulang kali):
 * 1. Membuat database bila belum ada (khusus lokal; di Railway database sudah disediakan).
 * 2. Membuat tabel dari database/schema.sql (CREATE TABLE IF NOT EXISTS).
 * 3. Membuat akun admin dari ADMIN_USERNAME, ADMIN_EMAIL, ADMIN_PASSWORD bila akun belum ada
 *    (password akun yang sudah ada TIDAK pernah ditimpa).
 * 4. Membuat draft konten dari Default Content bila belum ada (konten aktif tidak ditimpa)
 *    dan baris pengaturan undangan. Undangan publik tampil setelah admin menekan Publish.
 */

use App\Core\Database;
use App\Models\InvitationSetting;
use App\Services\ContentRepository;

require dirname(__DIR__).'/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit("Jalankan lewat terminal: php cli/install.php\n");
}

$c = config('app.database');
echo "Database: {$c['username']}@{$c['host']}:{$c['port']}/{$c['database']}\n";

// ---------- 1. Koneksi (buat database bila belum ada) ----------
try {
    Database::pdo();
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? null) !== 1049 && ! str_contains($e->getMessage(), 'Unknown database')) {
        fwrite(STDERR, 'Gagal terhubung ke MySQL: '.$e->getMessage()."\nPeriksa DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env\n");
        exit(1);
    }
    // Database belum ada: buat dengan charset utf8mb4
    $server = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $server->exec('CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $c['database']).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "- Database {$c['database']} dibuat.\n";
    Database::pdo();
}

// ---------- 2. Tabel ----------
$sql = file_get_contents(BASE_PATH.'/database/schema.sql');
$sql = preg_replace('/^--.*$/m', '', $sql); // buang komentar
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    Database::pdo()->exec($statement);
}
echo "- Tabel siap.\n";

// ---------- 3. Akun admin ----------
$admin = config('invitation.admin');
if (! filled($admin['email']) || ! filled($admin['username']) || ! filled($admin['password'])) {
    echo "! Akun admin TIDAK dibuat: isi ADMIN_USERNAME, ADMIN_EMAIL, dan ADMIN_PASSWORD di .env / Variables.\n";
} elseif (strlen($admin['password']) < 10) {
    echo "! ADMIN_PASSWORD minimal 10 karakter. Akun admin tidak dibuat.\n";
} else {
    $email = mb_strtolower($admin['email']);
    if (Database::value('SELECT id FROM users WHERE email = ?', [$email])) {
        echo "- Akun admin {$email} sudah ada (password tidak diubah).\n";
    } else {
        $now = now_db();
        // Password disimpan sebagai hash bcrypt (cost 12, sama dengan Laravel)
        Database::insert(
            'INSERT INTO users (name, username, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$admin['name'], mb_strtolower($admin['username']), $email, password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]), $now, $now]
        );
        echo "- Akun admin {$email} dibuat.\n";
    }
}

// ---------- 4. Konten default & pengaturan ----------
(new ContentRepository())->ensureDraft();
InvitationSetting::current();
echo "- Draft konten & pengaturan siap.\n";
echo "Selesai. Buka /admin/login, periksa konten, lalu klik Publish agar undangan tampil ke tamu.\n";
