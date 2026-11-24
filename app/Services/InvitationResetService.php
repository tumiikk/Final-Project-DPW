<?php

namespace App\Services;

use App\Core\Database;
use App\Support\DefaultContent;

/**
 * Reset hanya menyentuh DRAFT konten undangan. Tidak pernah menyentuh
 * guests, guest_responses, users, atau snapshot published (landing publik baru berubah setelah Publish).
 */
final class InvitationResetService
{
    private ContentRepository $content;

    public function __construct()
    {
        $this->content = new ContentRepository();
    }

    /** Mengembalikan satu section draft ke Default Content. */
    public function resetSection(string $key): array
    {
        $default = DefaultContent::section($key);

        $draft = Database::transaction(fn () => $this->content->replaceDraftSection($key, $default));
        $this->purge();

        return $draft;
    }

    /** Mengembalikan seluruh konten draft ke Default Content. */
    public function resetAll(): array
    {
        $draft = Database::transaction(fn () => $this->content->replaceDraftAll(DefaultContent::all()));
        $this->purge();

        return $draft;
    }

    /** Setelah commit: kegagalan menghapus file tidak membatalkan reset. */
    private function purge(): void
    {
        try {
            (new MediaService())->purgeOrphans();
        } catch (\Throwable $e) {
            log_error('Pembersihan media gagal: '.$e->getMessage());
        }
    }
}
