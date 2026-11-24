<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tabel `guests`: daftar tamu + token link undangan personal.
 * Relasi: satu tamu punya maksimal satu baris di `guest_responses` (RSVP & ucapan).
 *
 * Data tamu dikembalikan sebagai array. Kunci 'response' berisi RSVP tamu (array) atau null.
 */
final class Guest
{
    /** Panjang token link personal, contoh /undangan/abcdefghij0123456789wxyz */
    public const TOKEN_LENGTH = 24;

    // Kolom yang diambil bersama data RSVP (LEFT JOIN), diberi awalan r_ agar tidak bentrok.
    private const SELECT = 'SELECT g.*, r.id AS r_id, r.attendance AS r_attendance, r.attendee_count AS r_attendee_count,
            r.message AS r_message, r.is_hidden AS r_is_hidden, r.submitted_at AS r_submitted_at
        FROM guests g LEFT JOIN guest_responses r ON r.guest_id = g.id';

    /** Mengambil tamu berdasarkan id (beserta RSVP-nya). */
    public static function find(int $id): ?array
    {
        $row = Database::one(self::SELECT.' WHERE g.id = ?', [$id]);

        return $row ? self::map($row) : null;
    }

    /** Mengambil tamu dari token link yang masih AKTIF. Token salah / link nonaktif => null. */
    public static function findActiveByToken(string $token): ?array
    {
        $row = Database::one(self::SELECT.' WHERE g.token = ? AND g.is_link_active = 1', [$token]);

        return $row ? self::map($row) : null;
    }

    /**
     * Menyusun kondisi WHERE dari filter pencarian dashboard (dipakai daftar tamu & export).
     * q = nama mengandung teks; status = hadir | tidak_hadir | belum; link = active | inactive.
     *
     * @return array{0: string, 1: array} [potongan SQL, parameter]
     */
    public static function filterSql(array $f): array
    {
        $where = [];
        $params = [];

        if (! empty($f['q'])) {
            // addcslashes: tanda % dan _ dari input dianggap huruf biasa, bukan wildcard LIKE
            $where[] = 'g.name LIKE ?';
            $params[] = '%'.addcslashes($f['q'], '%_\\').'%';
        }
        if (! empty($f['status'])) {
            if ($f['status'] === 'belum') {
                $where[] = 'r.id IS NULL'; // belum punya RSVP
            } else {
                $where[] = 'r.attendance = ?';
                $params[] = $f['status'];
            }
        }
        if (! empty($f['link'])) {
            $where[] = 'g.is_link_active = ?';
            $params[] = $f['link'] === 'active' ? 1 : 0;
        }

        return [$where ? ' WHERE '.implode(' AND ', $where) : '', $params];
    }

    /** Semua tamu sesuai filter, urut nama (dipakai export). */
    public static function all(array $filters, string $orderBy = 'g.name ASC'): array
    {
        [$where, $params] = self::filterSql($filters);

        return array_map([self::class, 'map'], Database::all(self::SELECT.$where.' ORDER BY '.$orderBy, $params));
    }

    /**
     * Daftar tamu per halaman (25 per halaman, terbaru dulu) dengan format JSON yang sama
     * seperti paginator Laravel: { data, current_page, last_page, total, per_page, from, to }.
     */
    public static function paginate(array $filters, int $page, int $perPage = 25): array
    {
        [$where, $params] = self::filterSql($filters);
        $total = (int) Database::value('SELECT COUNT(*) FROM guests g LEFT JOIN guest_responses r ON r.guest_id = g.id'.$where, $params);
        $offset = (max(1, $page) - 1) * $perPage;

        $rows = Database::all(self::SELECT.$where.' ORDER BY g.id DESC LIMIT '.(int) $perPage.' OFFSET '.(int) $offset, $params);

        return paginator(array_map([self::class, 'map'], $rows), $total, $page, $perPage);
    }

    /** URL lengkap undangan personal tamu. */
    public static function url(array $guest): string
    {
        return absolute_url('/undangan/'.$guest['token']);
    }

    /** Status RSVP: hadir | tidak_hadir | belum */
    public static function rsvpStatus(array $guest): string
    {
        return $guest['response']['attendance'] ?? 'belum';
    }

    /** Menyimpan perubahan nama / batas jumlah hadir / catatan. */
    public static function update(int $id, array $data): void
    {
        $sets = [];
        $params = [];
        foreach (['name', 'max_attendees', 'notes'] as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if ($sets === []) {
            return;
        }
        $params[] = now_db();
        $params[] = $id;

        Database::query('UPDATE guests SET '.implode(', ', $sets).', updated_at = ? WHERE id = ?', $params);
    }

    /** Menghapus tamu. RSVP & ucapannya ikut terhapus (FOREIGN KEY ... ON DELETE CASCADE). */
    public static function delete(int $id): void
    {
        Database::query('DELETE FROM guests WHERE id = ?', [$id]);
    }

    /** Mengubah baris database menjadi array tamu dengan tipe data yang benar. */
    private static function map(array $row): array
    {
        $response = $row['r_id'] === null ? null : [
            'id' => (int) $row['r_id'],
            'attendance' => $row['r_attendance'],
            'attendee_count' => (int) $row['r_attendee_count'],
            'message' => $row['r_message'],
            'is_hidden' => (bool) $row['r_is_hidden'],
            'submitted_at' => $row['r_submitted_at'],
        ];

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'token' => $row['token'],
            'is_link_active' => (bool) $row['is_link_active'],
            'link_regenerated_at' => $row['link_regenerated_at'],
            'max_attendees' => (int) $row['max_attendees'],
            'notes' => $row['notes'],
            'response' => $response,
        ];
    }
}
