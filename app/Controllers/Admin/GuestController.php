<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Guest;
use App\Services\GuestLinkService;

/**
 * Menu "Data tamu & link": daftar, tambah, edit, hapus tamu, serta kelola link undangan personal
 * (salin, nonaktifkan/aktifkan, buat ulang). Fitur QR Code sudah dihapus.
 */
class GuestController
{
    private GuestLinkService $links;

    public function __construct()
    {
        $this->links = new GuestLinkService();
    }

    /** Halaman daftar tamu, atau data JSON (pencarian, filter, 25 per halaman) untuk JavaScript. */
    public function index(): void
    {
        if (! expects_json()) {
            html_response(view('admin.guests'));

            return;
        }

        $data = Validator::validate(request_input(), [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:hadir,tidak_hadir,belum'],
            'link' => ['nullable', 'in:active,inactive'],
        ]);

        $page = max(1, (int) input('page', 1));
        $result = Guest::paginate($data, $page);
        $result['data'] = array_map(fn ($g) => $this->present($g), $result['data']);

        json_response($result);
    }

    /** Menambah tamu; link personal langsung dibuat. */
    public function store(): void
    {
        $data = Validator::validate(request_input(), $this->rules());
        $guest = $this->links->create($data + ['max_attendees' => 1]);

        json_response($this->present($guest), 201);
    }

    public function show(string $id): void
    {
        json_response($this->present($this->findOrFail($id)));
    }

    public function update(string $id): void
    {
        $guest = $this->findOrFail($id);
        $data = Validator::validate(request_input(), $this->rules());
        if (array_key_exists('max_attendees', $data) && $data['max_attendees'] === null) {
            unset($data['max_attendees']); // kolom wajib berisi angka
        }

        Guest::update($guest['id'], $data);

        json_response($this->present(Guest::find($guest['id'])));
    }

    public function destroy(string $id): void
    {
        Guest::delete($this->findOrFail($id)['id']); // respons ikut terhapus (cascade)

        no_content();
    }

    /** Buat ulang link: token lama langsung tidak berlaku. */
    public function regenerate(string $id): void
    {
        json_response($this->present($this->links->regenerate($this->findOrFail($id)['id'])));
    }

    /** Aktifkan / nonaktifkan link tanpa menghapus data tamu. */
    public function toggleLink(string $id): void
    {
        $guest = $this->findOrFail($id);
        $data = Validator::validate(request_input(), ['active' => ['required', 'boolean']]);

        json_response($this->present($this->links->setActive($guest['id'], (bool) $data['active'])));
    }

    /** Aturan validasi tamu (pengganti GuestRequest). */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'max_attendees' => ['nullable', 'integer', 'between:1,20'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function findOrFail(string $id): array
    {
        return Guest::find((int) $id) ?? abort(404);
    }

    /** Format data tamu yang dikirim ke JavaScript dashboard. */
    private function present(array $guest): array
    {
        $r = $guest['response'];

        return [
            'id' => $guest['id'],
            'name' => $guest['name'],
            'url' => Guest::url($guest),
            'is_link_active' => $guest['is_link_active'],
            'link_regenerated_at' => to_iso($guest['link_regenerated_at']),
            'max_attendees' => $guest['max_attendees'],
            'notes' => $guest['notes'],
            'rsvp_status' => Guest::rsvpStatus($guest),
            'attendee_count' => $r['attendee_count'] ?? null,
            'message' => $r['message'] ?? null,
            'submitted_at' => to_iso($r['submitted_at'] ?? null),
        ];
    }
}
