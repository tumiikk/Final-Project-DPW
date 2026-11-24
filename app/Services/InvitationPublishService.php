<?php

namespace App\Services;

use App\Core\Database;
use App\Models\InvitationSetting;
use App\Models\InvitationSnapshot;
use App\Support\DefaultContent;

/** Publish draft ke tamu, batalkan perubahan draft, dan status draft vs published. */
final class InvitationPublishService
{
    private ContentRepository $content;

    private MediaService $media;

    public function __construct()
    {
        $this->content = new ContentRepository();
        $this->media = new MediaService();
    }

    /** Menyalin draft ke published dalam satu transaction. */
    public function publish(): array
    {
        $this->content->ensureDraft();

        $published = Database::transaction(function () {
            $draft = InvitationSnapshot::find(InvitationSnapshot::DRAFT, true);
            $row = InvitationSnapshot::find(InvitationSnapshot::PUBLISHED, true)
                ?? ['id' => null, 'status' => InvitationSnapshot::PUBLISHED, 'version' => 0];

            $this->content->write($row, $this->content->normalize($draft['payload']));
            InvitationSetting::touchPublished();

            return $row;
        });

        $this->purge();

        return $published;
    }

    /** "Batalkan perubahan": draft dikembalikan ke versi published (atau default jika belum pernah publish). */
    public function discardDraft(): array
    {
        $payload = $this->content->published() ?? DefaultContent::all();

        $draft = Database::transaction(fn () => $this->content->replaceDraftAll($payload));
        $this->purge();

        return $draft;
    }

    /** @return array{state: string, has_published: bool, has_unpublished_changes: bool, draft_saved_at: ?string, published_at: ?string} */
    public function status(): array
    {
        $draft = $this->content->ensureDraft();
        $published = InvitationSnapshot::find(InvitationSnapshot::PUBLISHED);
        $changed = ! $published || $published['content_hash'] !== $draft['content_hash'];

        return [
            'state' => ! $published ? 'draft' : ($changed ? 'draft_ahead' : 'published'),
            'has_published' => (bool) $published,
            'has_unpublished_changes' => $changed,
            'draft_saved_at' => to_iso($draft['updated_at'] ?? null),
            'published_at' => to_iso(InvitationSetting::current()['published_at']),
        ];
    }

    /** Setelah commit: kegagalan menghapus file tidak membatalkan publish. */
    private function purge(): void
    {
        try {
            $this->media->purgeOrphans();
        } catch (\Throwable $e) {
            log_error('Pembersihan media gagal: '.$e->getMessage());
        }
    }
}
