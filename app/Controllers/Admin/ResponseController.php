<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\GuestResponse;

/** Menu "RSVP & ucapan": daftar respons, sembunyikan/tampilkan ucapan, hapus respons. */
class ResponseController
{
    public function index(): void
    {
        if (! expects_json()) {
            html_response(view('admin.responses'));

            return;
        }

        $data = Validator::validate(request_input(), [
            'q' => ['nullable', 'string', 'max:100'],
            'attendance' => ['nullable', 'in:hadir,tidak_hadir'],
            'hidden' => ['nullable', 'boolean'],
        ]);

        $result = GuestResponse::paginate($data, max(1, (int) input('page', 1)));
        $result['data'] = array_map(fn ($r) => [
            'id' => $r['id'],
            'guest_id' => $r['guest_id'],
            'guest_name' => $r['guest_name'],
            'attendance' => $r['attendance'],
            'attendee_count' => $r['attendee_count'],
            'message' => $r['message'],
            'is_hidden' => $r['is_hidden'],
            'submitted_at' => to_iso($r['submitted_at']),
        ], $result['data']);

        json_response($result);
    }

    public function setHidden(string $id): void
    {
        $response = GuestResponse::find((int) $id) ?? abort(404);
        $data = Validator::validate(request_input(), ['hidden' => ['required', 'boolean']]);
        GuestResponse::setHidden($response['id'], (bool) $data['hidden']);

        json_response(['id' => $response['id'], 'is_hidden' => (bool) $data['hidden']]);
    }

    public function destroy(string $id): void
    {
        $response = GuestResponse::find((int) $id) ?? abort(404);
        GuestResponse::delete($response['id']); // tamu kembali berstatus "belum konfirmasi"

        no_content();
    }
}
