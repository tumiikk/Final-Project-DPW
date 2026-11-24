<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Guest;

/**
 * Membuat tamu beserta link undangan personal (token unik), membuat ulang link,
 * dan mengaktifkan/menonaktifkan link.
 * Catatan migrasi: pembuat QR Code (qrSvg) sudah DIHAPUS sesuai permintaan; link personal tetap.
 */
final class GuestLinkService
{
    /** Menambah tamu baru dengan token acak. Dicoba ulang bila token kebetulan sudah dipakai. */
    public function create(array $data): array
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $now = now_db();
                // Menyimpan tamu baru (link otomatis aktif)
                $id = Database::insert(
                    'INSERT INTO guests (name, token, is_link_active, max_attendees, notes, created_at, updated_at) VALUES (?, ?, 1, ?, ?, ?, ?)',
                    [$data['name'], $this->newToken(), (int) ($data['max_attendees'] ?? 1), $data['notes'] ?? null, $now, $now]
                );

                return Guest::find($id);
            } catch (\PDOException $e) {
                if (! Database::isDuplicate($e)) {
                    throw $e;
                }
                // token bentrok (sangat jarang): coba token lain
            }
        }

        throw new \RuntimeException('Gagal membuat token unik untuk tamu.');
    }

    /** Token lama langsung tidak berlaku. Link baru otomatis aktif. */
    public function regenerate(int $guestId): array
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $now = now_db();
                Database::query(
                    'UPDATE guests SET token = ?, is_link_active = 1, link_regenerated_at = ?, updated_at = ? WHERE id = ?',
                    [$this->newToken(), $now, $now, $guestId]
                );

                return Guest::find($guestId);
            } catch (\PDOException $e) {
                if (! Database::isDuplicate($e)) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Gagal membuat token baru.');
    }

    /** Mengaktifkan / menonaktifkan link tanpa menghapus data tamu. */
    public function setActive(int $guestId, bool $active): array
    {
        Database::query('UPDATE guests SET is_link_active = ?, updated_at = ? WHERE id = ?', [$active ? 1 : 0, now_db(), $guestId]);

        return Guest::find($guestId);
    }

    /** Token 24 karakter huruf kecil + angka. */
    private function newToken(): string
    {
        return random_token(Guest::TOKEN_LENGTH);
    }
}
