<?php

namespace App\Controllers\Admin;

use App\Services\InvitationPublishService;

/** Status draft/published, tombol Publish, dan "Batalkan perubahan" draft. */
class PublishController
{
    public function status(): void
    {
        json_response((new InvitationPublishService())->status());
    }

    /** Menyalin draft menjadi versi yang dilihat tamu. */
    public function publish(): void
    {
        $publish = new InvitationPublishService();
        $publish->publish();

        json_response($publish->status());
    }

    /** Batalkan perubahan draft: kembali ke versi published. */
    public function discard(): void
    {
        $publish = new InvitationPublishService();
        $publish->discardDraft();

        json_response($publish->status());
    }
}
