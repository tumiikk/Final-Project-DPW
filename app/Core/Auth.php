<?php

namespace App\Core;

/**
 * Autentikasi admin berbasis session (pengganti Auth facade + middleware auth/guest Laravel).
 * Password diverifikasi dengan password_verify() terhadap hash bcrypt di tabel users,
 * sehingga akun yang dibuat oleh versi Laravel tetap dapat dipakai login.
 */
final class Auth
{
    private static ?array $user = null;

    /** Data admin yang sedang login, atau null. */
    public static function user(): ?array
    {
        if (self::$user === null && ! empty($_SESSION['admin_id'])) {
            self::$user = Database::one('SELECT id, name, username, email FROM users WHERE id = ?', [(int) $_SESSION['admin_id']]);
            if (! self::$user) {
                unset($_SESSION['admin_id']); // akun sudah dihapus
            }
        }

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Mencoba login dengan email ATAU username + password.
     * Berhasil: id session diganti (mencegah session fixation) lalu id admin disimpan di session.
     */
    public static function attempt(string $field, string $login, string $password): bool
    {
        $column = $field === 'email' ? 'email' : 'username';
        $user = Database::one("SELECT * FROM users WHERE $column = ? LIMIT 1", [$login]);

        if (! $user || ! password_verify($password, (string) $user['password'])) {
            return false;
        }

        // Perbarui hash bila algoritma/cost bawaan PHP berubah
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
            Database::query('UPDATE users SET password = ?, updated_at = ? WHERE id = ?', [password_hash($password, PASSWORD_BCRYPT), now_db(), $user['id']]);
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $user['id'];
        self::$user = null;

        return true;
    }

    /** Logout: hapus data session, buat id session & token CSRF baru. */
    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_token'] = bin2hex(random_bytes(20));
        self::$user = null;
    }

    /**
     * Middleware "auth": halaman admin wajib login.
     * Request JavaScript dijawab 401, browser biasa diarahkan ke halaman login
     * (URL tujuan disimpan agar setelah login kembali ke halaman tersebut).
     */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        if (expects_json()) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        if (request_method() === 'GET') {
            $_SESSION['url_intended'] = $_SERVER['REQUEST_URI'] ?? url('/admin/dashboard');
        }

        // Status 302 + header Location = alihkan ke halaman login
        throw new HttpException(302, '', ['Location' => url('/admin/login')]);
    }

    /** Middleware "guest": admin yang sudah login tidak perlu melihat halaman login lagi. */
    public static function requireGuest(): void
    {
        if (self::check()) {
            throw new HttpException(302, '', ['Location' => url('/admin/dashboard')]);
        }
    }
}
