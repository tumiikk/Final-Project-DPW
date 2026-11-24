<?php

namespace App\Core;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * Menyimpan session PHP di tabel `sessions` (tabel yang sama dengan SESSION_DRIVER=database di Laravel).
 *
 * Alasan memakai database, bukan file: di Railway sistem file container bersifat sementara,
 * sehingga session berbasis file akan hilang setiap redeploy/restart dan admin harus login ulang.
 */
final class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    // Penanda format payload milik aplikasi ini (baris session lama buatan Laravel diabaikan).
    private const PREFIX = 'native:';

    public function __construct(private int $lifetimeMinutes)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    /** Membaca isi session berdasarkan id cookie; kosong bila tidak ada / kedaluwarsa. */
    public function read(string $id): string|false
    {
        $row = Database::one('SELECT payload, last_activity FROM sessions WHERE id = ?', [$id]);
        if (! $row || $row['last_activity'] < time() - $this->lifetimeMinutes * 60) {
            return '';
        }

        $payload = base64_decode((string) $row['payload'], true);

        return ($payload !== false && str_starts_with($payload, self::PREFIX)) ? substr($payload, strlen(self::PREFIX)) : '';
    }

    /** Menyimpan / memperbarui session (INSERT bila baru, UPDATE bila sudah ada). */
    public function write(string $id, string $data): bool
    {
        Database::query(
            'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), ip_address = VALUES(ip_address),
                 user_agent = VALUES(user_agent), payload = VALUES(payload), last_activity = VALUES(last_activity)',
            [
                $id,
                $_SESSION['admin_id'] ?? null,
                substr(client_ip(), 0, 45),
                isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
                base64_encode(self::PREFIX.$data),
                time(),
            ]
        );

        return true;
    }

    public function destroy(string $id): bool
    {
        Database::query('DELETE FROM sessions WHERE id = ?', [$id]);

        return true;
    }

    /** Membersihkan session kedaluwarsa (dipanggil PHP secara acak, peluang 2%). */
    public function gc(int $max_lifetime): int|false
    {
        return Database::query('DELETE FROM sessions WHERE last_activity < ?', [time() - $this->lifetimeMinutes * 60])->rowCount();
    }

    /** Dipakai mode strict: id session dari cookie hanya diterima bila memang ada di database. */
    public function validateId(string $id): bool
    {
        return (bool) Database::value('SELECT 1 FROM sessions WHERE id = ? AND last_activity >= ?', [$id, time() - $this->lifetimeMinutes * 60]);
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }
}
