<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tabel `guest_responses`: RSVP + ucapan. Satu tamu = satu respons (kolom guest_id UNIQUE),
 * kirim ulang dari tamu yang sama memperbarui baris yang sama.
 * "Belum konfirmasi" = tamu yang tidak punya baris di tabel ini.
 */
final class GuestResponse
{
    public const HADIR = 'hadir';
    public const TIDAK_HADIR = 'tidak_hadir';

    /** Mengambil satu respons berdasarkan id. */
    public static function find(int $id): ?array
    {
        $row = Database::one('SELECT * FROM guest_responses WHERE id = ?', [$id]);

        return $row ? self::map($row) : null;
    }

    /**
     * Membuat respons baru atau memperbarui respons tamu yang sudah ada (pengganti updateOrCreate).
     * is_hidden tidak disentuh: keputusan moderasi admin tetap berlaku.
     */
    public static function upsertForGuest(int $guestId, string $attendance, int $count, ?string $message): array
    {
        $now = now_db();
        Database::query(
            'INSERT INTO guest_responses (guest_id, attendance, attendee_count, message, is_hidden, submitted_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, 0, ?, ?, ?)
             ON DUPLICATE KEY UPDATE attendance = VALUES(attendance), attendee_count = VALUES(attendee_count),
                 message = VALUES(message), submitted_at = VALUES(submitted_at), updated_at = VALUES(updated_at)',
            [$guestId, $attendance, $count, $message, $now, $now, $now]
        );

        return self::map(Database::one('SELECT * FROM guest_responses WHERE guest_id = ?', [$guestId]));
    }

    /** Menyembunyikan / menampilkan ucapan di undangan (moderasi admin). */
    public static function setHidden(int $id, bool $hidden): void
    {
        Database::query('UPDATE guest_responses SET is_hidden = ?, updated_at = ? WHERE id = ?', [$hidden ? 1 : 0, now_db(), $id]);
    }

    /** Menghapus respons: tamu kembali berstatus "belum konfirmasi". */
    public static function delete(int $id): void
    {
        Database::query('DELETE FROM guest_responses WHERE id = ?', [$id]);
    }

    /**
     * Daftar respons per halaman untuk menu "RSVP & ucapan" (terbaru dulu).
     * Filter: q (nama tamu), attendance (hadir | tidak_hadir), hidden (true/false).
     */
    public static function paginate(array $f, int $page, int $perPage = 25): array
    {
        $where = [];
        $params = [];
        if (! empty($f['q'])) {
            $where[] = 'g.name LIKE ?';
            $params[] = '%'.addcslashes($f['q'], '%_\\').'%';
        }
        if (! empty($f['attendance'])) {
            $where[] = 'r.attendance = ?';
            $params[] = $f['attendance'];
        }
        if (array_key_exists('hidden', $f) && $f['hidden'] !== null) {
            $where[] = 'r.is_hidden = ?';
            $params[] = filter_var($f['hidden'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }
        $sqlWhere = $where ? ' WHERE '.implode(' AND ', $where) : '';
        $from = ' FROM guest_responses r JOIN guests g ON g.id = r.guest_id';

        $total = (int) Database::value('SELECT COUNT(*)'.$from.$sqlWhere, $params);
        $offset = (max(1, $page) - 1) * $perPage;
        $rows = Database::all('SELECT r.*, g.name AS guest_name'.$from.$sqlWhere.' ORDER BY r.submitted_at DESC LIMIT '.(int) $perPage.' OFFSET '.(int) $offset, $params);

        return paginator(array_map(fn ($r) => self::map($r) + ['guest_name' => $r['guest_name']], $rows), $total, $page, $perPage);
    }

    /** Statistik untuk halaman Ringkasan dashboard (semua angka langsung dari database). */
    public static function stats(): array
    {
        $total = (int) Database::value('SELECT COUNT(*) FROM guests');
        $responded = (int) Database::value('SELECT COUNT(*) FROM guest_responses');

        return [
            'total_guests' => $total,
            'responded' => $responded,
            'attending' => (int) Database::value('SELECT COUNT(*) FROM guest_responses WHERE attendance = ?', [self::HADIR]),
            'attending_people' => (int) Database::value('SELECT COALESCE(SUM(attendee_count), 0) FROM guest_responses WHERE attendance = ?', [self::HADIR]),
            'not_attending' => (int) Database::value('SELECT COUNT(*) FROM guest_responses WHERE attendance = ?', [self::TIDAK_HADIR]),
            'pending' => max($total - $responded, 0),
            'wishes' => (int) Database::value("SELECT COUNT(*) FROM guest_responses WHERE message IS NOT NULL AND message != ''"),
        ];
    }

    /** Ucapan untuk export, terbaru dulu, opsional disaring berdasarkan nama tamu. */
    public static function wishesForExport(?string $q): array
    {
        $sql = "SELECT r.*, g.name AS guest_name FROM guest_responses r JOIN guests g ON g.id = r.guest_id
                WHERE r.message IS NOT NULL AND r.message != ''";
        $params = [];
        if (! empty($q)) {
            $sql .= ' AND g.name LIKE ?';
            $params[] = '%'.addcslashes($q, '%_\\').'%';
        }

        return array_map(fn ($r) => self::map($r) + ['guest_name' => $r['guest_name']], Database::all($sql.' ORDER BY r.submitted_at DESC', $params));
    }

    /** Ucapan yang boleh tampil publik: tidak disembunyikan admin, terbaru dulu. */
    public static function visibleWishes(int $limit): array
    {
        $rows = Database::all(
            "SELECT r.id, r.message, g.name FROM guest_responses r LEFT JOIN guests g ON g.id = r.guest_id
             WHERE r.is_hidden = 0 AND r.message IS NOT NULL AND r.message != ''
             ORDER BY r.submitted_at DESC LIMIT ".(int) $limit
        );

        return array_map(fn ($r) => ['key' => (int) $r['id'], 'name' => $r['name'] ?? 'Tamu', 'message' => $r['message']], $rows);
    }

    private static function map(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'guest_id' => (int) $row['guest_id'],
            'attendance' => $row['attendance'],
            'attendee_count' => (int) $row['attendee_count'],
            'message' => $row['message'],
            'is_hidden' => (bool) $row['is_hidden'],
            'submitted_at' => $row['submitted_at'],
        ];
    }
}
