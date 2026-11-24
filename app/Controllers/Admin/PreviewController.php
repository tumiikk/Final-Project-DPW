<?php

namespace App\Controllers\Admin;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Services\ContentRepository;
use App\Support\DefaultContent;

/**
 * Preview memakai template yang SAMA dengan landing (views/invitation/page.php & sections/*).
 * Editor mengirim data yang belum disimpan, server merender HTML, lalu iframe menggantinya.
 */
class PreviewController
{
    private ContentRepository $content;

    public function __construct()
    {
        $this->content = new ContentRepository();
    }

    /** Dokumen iframe: memuat CSS landing, lalu menunggu HTML dari editor lewat postMessage. */
    public function frame(): void
    {
        $full = filter_var(input('full', false), FILTER_VALIDATE_BOOLEAN);
        $html = $full ? $this->renderPage($this->content->normalize($this->content->draft())) : '';

        html_response(view('admin.preview-frame', ['html' => $html]));
    }

    /** Preview seluruh undangan (versi draft). */
    public function full(): void
    {
        html_response(view('admin.preview-full'));
    }

    /** Merender satu section dari data editor yang belum disimpan, hasilnya HTML dalam JSON. */
    public function render(): void
    {
        $section = (string) input('section', '');
        if (! in_array($section, DefaultContent::sections(), true)) {
            abort(404);
        }

        $data = input('data');
        if (! is_array($data)) {
            abort(422);
        }

        $payload = $this->content->normalize(array_replace($this->content->draft(), [$section => $data]));
        $html = $section === 'music' ? '' : $this->renderSection($section, $payload);

        json_response(['html' => $html]);
    }

    /** Variabel tampilan preview: tamu contoh agar form RSVP & nama tamu ikut terlihat. */
    private function vars(array $payload): array
    {
        $guest = ['id' => 0, 'name' => 'Nama Tamu Undangan', 'token' => str_repeat('x', Guest::TOKEN_LENGTH), 'max_attendees' => 3, 'response' => null];

        return ['payload' => $payload, 'guest' => $guest, 'wishes' => GuestResponse::visibleWishes(500), 'preview' => true];
    }

    private function renderSection(string $section, array $payload): string
    {
        return view('invitation.sections.'.$section, $this->vars($payload));
    }

    private function renderPage(array $payload): string
    {
        return view('invitation.page', $this->vars($payload));
    }
}
