<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Services\InvitationPublishService;
use App\Services\InvitationResetService;

/** Reset konten draft ke Default Content (per section atau seluruhnya). Data tamu/RSVP tidak tersentuh. */
class ResetController
{
    public function section(string $section): void
    {
        $draft = (new InvitationResetService())->resetSection($section);

        json_response(['reset' => $section, 'version' => $draft['version'], 'content' => (new InvitationPublishService())->status()]);
    }

    /** Reset semua section. Wajib mengirim confirm=true agar tidak terjadi tanpa sengaja. */
    public function all(): void
    {
        Validator::validate(request_input(), ['confirm' => ['accepted']]);

        $draft = (new InvitationResetService())->resetAll();

        json_response(['reset' => 'all', 'version' => $draft['version'], 'content' => (new InvitationPublishService())->status()]);
    }
}
