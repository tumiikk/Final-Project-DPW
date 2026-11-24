<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tabel `invitation_snapshots`: maksimal dua baris, 'draft' (yang diedit admin) dan 'published'
 * (yang dilihat tamu). Kolom payload berisi JSON seluruh konten landing.
 *
 * Baris dikembalikan sebagai array; 'payload' sudah di-decode menjadi array PHP.
 */
final class InvitationSnapshot
{
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';

    /**
     * Mengambil baris berdasarkan status.
     * $forUpdate = true mengunci baris (SELECT ... FOR UPDATE) selama transaksi,
     * agar dua admin yang menyimpan bersamaan tidak saling menimpa.
     */
    public static function find(string $status, bool $forUpdate = false): ?array
    {
        $row = Database::one('SELECT * FROM invitation_snapshots WHERE status = ?'.($forUpdate ? ' FOR UPDATE' : ''), [$status]);

        return $row ? self::map($row) : null;
    }

    /** Menyimpan baris: INSERT bila belum punya id, UPDATE bila sudah ada. */
    public static function save(array &$row): void
    {
        $now = now_db();
        // JSON tanpa flag khusus, sama seperti cast 'array' Eloquent
        $json = json_encode($row['payload'], JSON_THROW_ON_ERROR);

        if (empty($row['id'])) {
            $row['id'] = Database::insert(
                'INSERT INTO invitation_snapshots (status, payload, content_hash, version, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$row['status'], $json, $row['content_hash'], $row['version'], $now, $now]
            );
            $row['created_at'] = $now;
        } else {
            Database::query(
                'UPDATE invitation_snapshots SET payload = ?, content_hash = ?, version = ?, updated_at = ? WHERE id = ?',
                [$json, $row['content_hash'], $row['version'], $now, $row['id']]
            );
        }
        $row['updated_at'] = $now;
    }

    /** Semua payload (draft & published), dipakai saat mencari file upload yang masih dipakai. */
    public static function allPayloads(): array
    {
        $rows = Database::all('SELECT payload FROM invitation_snapshots');

        return array_map(fn ($r) => json_decode((string) $r['payload'], true) ?: [], $rows);
    }

    private static function map(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['version'] = (int) $row['version'];
        $row['payload'] = json_decode((string) $row['payload'], true) ?: [];

        return $row;
    }
}
