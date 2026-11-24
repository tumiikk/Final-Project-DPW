<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Support\XlsxWriter;

/** Export daftar tamu, status RSVP, jumlah kehadiran, dan ucapan ke Excel (.xlsx) atau CSV. */
class ExportController
{
    private const TYPES = ['guests' => 'Daftar Tamu', 'rsvp' => 'Status RSVP', 'attendance' => 'Jumlah Kehadiran', 'wishes' => 'Ucapan Tamu'];

    public function export(string $type): void
    {
        if (! isset(self::TYPES[$type])) {
            abort(404);
        }

        // Filter yang sama dengan halaman daftar tamu
        $data = Validator::validate(request_input(), [
            'format' => ['required', 'in:xlsx,csv'],
            'status' => ['nullable', 'in:hadir,tidak_hadir,belum'],
            'link' => ['nullable', 'in:active,inactive'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $rows = match ($type) {
            'guests' => $this->guests($data, false),
            'rsvp' => $this->guests($data, true),
            'attendance' => $this->attendance($data),
            'wishes' => $this->wishes($data),
        };

        $name = 'undangan-'.$type.'-'.date('Ymd-His').'.'.$data['format'];

        if ($data['format'] === 'csv') {
            $body = XlsxWriter::csv($rows);
            $mime = 'text/csv; charset=UTF-8';
        } else {
            $body = XlsxWriter::build(self::TYPES[$type], $rows);
            $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        // Kirim sebagai file unduhan
        http_response_code(200);
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Length: '.strlen($body));
        echo $body;
    }

    private function label(?string $s): string
    {
        return match ($s) { 'hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir', default => 'Belum Konfirmasi' };
    }

    /** Format waktu kirim di file export (zona waktu aplikasi). */
    private function time(?string $db): string
    {
        return $db ? (new \DateTimeImmutable($db))->format('Y-m-d H:i') : '';
    }

    private function guests(array $f, bool $onlyResponded): array
    {
        $rows = [['Nama Tamu', 'Link Undangan', 'Status Link', 'Status RSVP', 'Jumlah Hadir', 'Ucapan', 'Ucapan Disembunyikan', 'Waktu Kirim', 'Catatan']];
        foreach (Guest::all($f) as $g) {
            $r = $g['response'];
            if ($onlyResponded && ! $r) {
                continue;
            }
            $rows[] = [$g['name'], Guest::url($g), $g['is_link_active'] ? 'Aktif' : 'Nonaktif', $this->label($r['attendance'] ?? null), $r ? (int) $r['attendee_count'] : '',
                $r['message'] ?? '', $r ? ($r['is_hidden'] ? 'Ya' : 'Tidak') : '', $this->time($r['submitted_at'] ?? null), $g['notes'] ?? ''];
        }

        return $rows;
    }

    private function attendance(array $f): array
    {
        $guests = Guest::all($f);
        $hadir = array_filter($guests, fn ($g) => ($g['response']['attendance'] ?? null) === 'hadir');

        $rows = [['Ringkasan', 'Jumlah']];
        $rows[] = ['Total tamu', count($guests)];
        $rows[] = ['Sudah konfirmasi', count(array_filter($guests, fn ($g) => $g['response'] !== null))];
        $rows[] = ['Tamu hadir (undangan)', count($hadir)];
        $rows[] = ['Total orang hadir', (int) array_sum(array_map(fn ($g) => $g['response']['attendee_count'], $hadir))];
        $rows[] = ['Tidak hadir', count(array_filter($guests, fn ($g) => ($g['response']['attendance'] ?? null) === 'tidak_hadir'))];
        $rows[] = ['Belum konfirmasi', count(array_filter($guests, fn ($g) => $g['response'] === null))];
        $rows[] = ['', ''];
        $rows[] = ['Nama Tamu', 'Status RSVP', 'Jumlah Hadir'];
        foreach ($guests as $g) {
            $rows[] = [$g['name'], $this->label($g['response']['attendance'] ?? null), $g['response'] ? (int) $g['response']['attendee_count'] : ''];
        }

        return $rows;
    }

    private function wishes(array $f): array
    {
        $rows = [['Nama Tamu', 'Ucapan', 'Status Tampil', 'Waktu Kirim']];
        foreach (GuestResponse::wishesForExport($f['q'] ?? null) as $r) {
            $rows[] = [$r['guest_name'], $r['message'], $r['is_hidden'] ? 'Disembunyikan' : 'Tampil', $this->time($r['submitted_at'])];
        }

        return $rows;
    }
}
