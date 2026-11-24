<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tabel `media_files`: mencatat setiap file upload (foto & lagu) agar
 * (1) konten hanya boleh merujuk file yang benar-benar diunggah lewat dashboard, dan
 * (2) file yang tidak lagi dipakai dapat dihapus dengan aman. File default TIDAK dicatat di sini.
 */
final class MediaFile
{
    /** Mencatat file baru, mengembalikan id-nya. */
    public static function create(array $d): int
    {
        $now = now_db();

        return Database::insert(
            'INSERT INTO media_files (kind, disk, path, original_name, mime, size, width, height, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$d['kind'], $d['disk'], $d['path'], $d['original_name'], $d['mime'], $d['size'], $d['width'] ?? null, $d['height'] ?? null, $now, $now]
        );
    }

    /** Jenis file (image/audio) untuk daftar path: ['invitations/cover/x.jpg' => 'image', ...]. */
    public static function kindsByPath(array $paths): array
    {
        if ($paths === []) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($paths), '?'));
        $rows = Database::all("SELECT path, kind FROM media_files WHERE path IN ($marks)", array_values($paths));

        return array_column($rows, 'kind', 'path');
    }

    /** File yang diunggah sebelum batas waktu tertentu (null = semua). */
    public static function createdBefore(?string $before): array
    {
        return $before === null
            ? Database::all('SELECT * FROM media_files')
            : Database::all('SELECT * FROM media_files WHERE created_at < ?', [$before]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM media_files WHERE id = ?', [$id]);
    }
}
