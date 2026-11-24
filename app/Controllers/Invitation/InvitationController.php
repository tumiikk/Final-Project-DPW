<?php

namespace App\Controllers\Invitation;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\InvitationSetting;
use App\Services\ContentRepository;

/** Halaman undangan publik: undangan umum (/) dan undangan personal tamu (/undangan/{token}). */
class InvitationController
{
    /** domain.com : undangan umum, tanpa nama tamu dan tanpa form RSVP. */
    public function landing(): void
    {
        $this->render(null);
    }

    /** domain.com/undangan/{token} : token tidak ada / link dinonaktifkan => halaman yang sama (tidak membocorkan alasan). */
    public function show(string $token): void
    {
        // Mencari tamu pemilik token yang link-nya masih aktif
        $guest = Guest::findActiveByToken($token);

        if (! $guest) {
            html_response(view('invitation.unavailable', ['reason' => 'link']), 404);

            return;
        }

        $this->render($guest);
    }

    /** Menampilkan versi PUBLISHED. Bila belum pernah publish atau undangan dimatikan admin => 503. */
    private function render(?array $guest): void
    {
        $payload = (new ContentRepository())->published();

        if ($payload === null || ! InvitationSetting::current()['is_online']) {
            html_response(view('invitation.unavailable', ['reason' => 'offline']), 503);

            return;
        }

        // Ucapan tamu yang tidak disembunyikan admin (maks. 500 terbaru; ditampilkan dalam kotak yang dapat digulir)
        $wishes = GuestResponse::visibleWishes(500);

        html_response(view('invitation.show', ['payload' => $payload, 'guest' => $guest, 'wishes' => $wishes, 'preview' => false]));
    }
}
