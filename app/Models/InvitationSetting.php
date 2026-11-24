<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tabel `invitation_settings`: satu baris (id = 1) berisi pengaturan operasional website,
 * yaitu status online/offline undangan dan waktu publish terakhir.
 */
final class InvitationSetting
{
    /** Mengambil baris pengaturan; dibuat otomatis (online) bila belum ada. */
    public static function current(): array
    {
        $row = Database::one('SELECT * FROM invitation_settings WHERE id = 1');
        if (! $row) {
            $now = now_db();
            // INSERT IGNORE: aman bila dua request membuatnya bersamaan
            Database::query('INSERT IGNORE INTO invitation_settings (id, is_online, created_at, updated_at) VALUES (1, 1, ?, ?)', [$now, $now]);
            $row = Database::one('SELECT * FROM invitation_settings WHERE id = 1');
        }
        $row['is_online'] = (bool) $row['is_online'];

        return $row;
    }

    /** Menyalakan / mematikan akses publik ke undangan. */
    public static function setOnline(bool $online): void
    {
        self::current();
        Database::query('UPDATE invitation_settings SET is_online = ?, updated_at = ? WHERE id = 1', [$online ? 1 : 0, now_db()]);
    }

    /** Mencatat waktu publish terakhir. */
    public static function touchPublished(): void
    {
        self::current();
        $now = now_db();
        Database::query('UPDATE invitation_settings SET published_at = ?, updated_at = ? WHERE id = 1', [$now, $now]);
    }
}
