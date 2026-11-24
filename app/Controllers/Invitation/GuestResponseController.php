<?php

namespace App\Controllers\Invitation;

use App\Core\HttpException;
use App\Core\Validator;
use App\Models\Guest;
use App\Models\GuestResponse;

/**
 * Menyimpan RSVP & ucapan dari halaman undangan personal.
 * Identitas tamu SELALU berasal dari token di URL. Field "name" pada form hanya tampilan
 * dan tidak dipakai, sehingga tamu tidak bisa mengubah identitas tamu lain.
 */
class GuestResponseController
{
    public function store(string $token): void
    {
        // Token harus milik tamu dengan link aktif; selain itu ditolak (403)
        $guest = Guest::findActiveByToken($token);
        if (! $guest) {
            throw new HttpException(403, 'This action is unauthorized.');
        }

        // Validasi input (jumlah hadir dibatasi sesuai jatah tamu)
        $max = $guest['max_attendees'] ?: 1;
        $data = Validator::validate(request_input(), [
            'attendance' => ['required', 'in:'.GuestResponse::HADIR.','.GuestResponse::TIDAK_HADIR],
            'attendee_count' => ['required_if:attendance,'.GuestResponse::HADIR, 'nullable', 'integer', "between:1,$max"],
            'message' => ['nullable', 'string', 'max:500'],
        ], [
            'attendance.required' => 'Pilih konfirmasi kehadiran.',
            'attendee_count.required_if' => 'Isi jumlah orang yang hadir.',
            'attendee_count.between' => 'Jumlah yang hadir melebihi batas untuk undangan ini.',
            'message.max' => 'Ucapan maksimal 500 karakter.',
        ]);

        $attending = $data['attendance'] === GuestResponse::HADIR;

        // Satu tamu = satu respons; kirim ulang memperbarui respons yang sama.
        $response = GuestResponse::upsertForGuest(
            $guest['id'],
            $data['attendance'],
            $attending ? (int) $data['attendee_count'] : 0,
            $this->clean($data['message'] ?? null)
        );

        if (expects_json()) {
            json_response(['ok' => true, 'wish' => ($response['message'] && ! $response['is_hidden'])
                ? ['key' => $response['id'], 'name' => $guest['name'], 'message' => $response['message']] : null]);

            return;
        }

        // Tanpa JavaScript: kembali ke halaman undangan bagian RSVP dengan pesan sukses
        flash('rsvp_status', 'ok');
        redirect(previous_url('/undangan/'.$token).'#rsvp');
    }

    /** Membuang karakter kontrol tak terlihat dan spasi di awal/akhir ucapan. */
    private function clean(?string $message): ?string
    {
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $message);
        $message = trim($message);

        return $message === '' ? null : $message;
    }
}
