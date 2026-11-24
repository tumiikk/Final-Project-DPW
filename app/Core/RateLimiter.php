<?php

namespace App\Core;

/**
 * Pembatas jumlah percobaan (pengganti middleware throttle Laravel).
 * Hitungan disimpan di tabel `cache` sehingga tetap berlaku walau server di-restart.
 *
 * Aturan (sama dengan AppServiceProvider asli):
 * - login : 5 percobaan / menit per (login + IP)
 * - rsvp  : 6 kiriman / menit per (token tamu + IP)
 */
final class RateLimiter
{
    /** Middleware throttle. Melempar 429 bila batas terlampaui, jika belum menambah hitungan. */
    public static function throttle(string $name, array $params): void
    {
        [$max, $key] = match ($name) {
            'login' => [5, mb_strtolower((string) input('login')).'|'.client_ip()],
            'rsvp' => [6, ($params['token'] ?? '').'|'.client_ip()],
            default => throw new \InvalidArgumentException("Limiter tidak dikenal: $name"),
        };

        $cacheKey = 'throttle:'.$name.':'.sha1($key);
        $now = time();

        $row = Database::one('SELECT value, expiration FROM cache WHERE `key` = ?', [$cacheKey]);
        $hits = ($row && (int) $row['expiration'] > $now) ? (int) $row['value'] : 0;

        if ($hits >= $max) {
            $retry = max(1, (int) $row['expiration'] - $now);
            throw new HttpException(429, 'Too Many Attempts.', ['Retry-After' => (string) $retry]);
        }

        // Percobaan pertama dalam jendela 60 detik membuat baris baru; berikutnya menambah hitungan.
        if ($hits === 0) {
            Database::query(
                'INSERT INTO cache (`key`, value, expiration) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), expiration = VALUES(expiration)',
                [$cacheKey, '1', $now + 60]
            );
        } else {
            Database::query('UPDATE cache SET value = CAST(value AS UNSIGNED) + 1 WHERE `key` = ?', [$cacheKey]);
        }

        // Bersihkan baris kedaluwarsa sesekali agar tabel tidak membengkak
        if (random_int(1, 50) === 1) {
            Database::query('DELETE FROM cache WHERE `key` LIKE ? AND expiration < ?', ['throttle:%', $now]);
        }
    }
}
