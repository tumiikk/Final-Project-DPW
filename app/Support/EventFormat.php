<?php

namespace App\Support;

/**
 * Format tanggal/jam acara untuk landing (bahasa Indonesia). Data selalu dari field event, tidak ada nilai karangan.
 * Versi PHP Native: nama hari & bulan Indonesia ditulis langsung (pengganti Carbon translatedFormat locale "id"),
 * hasilnya sama persis, contoh "Rabu, 30 September 2026".
 */
final class EventFormat
{
    private const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const MONTHS = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    public static function start(array $event): ?\DateTimeImmutable
    {
        return EventTime::parse($event['starts_at'] ?? null, $event['timezone'] ?? null);
    }

    /** "Rabu, 30 September 2026" atau null */
    public static function date(array $event): ?string
    {
        $d = self::start($event);
        if (! $d) {
            return null;
        }

        // format('w') = 0 (Minggu) s.d. 6 (Sabtu); format('n') = 1 s.d. 12
        return self::DAYS[(int) $d->format('w')].', '.$d->format('j').' '.self::MONTHS[(int) $d->format('n')].' '.$d->format('Y');
    }

    /** "Pukul 08 : 00 - Selesai" atau null jika jam mulai belum diisi */
    public static function time(array $event): ?string
    {
        $start = self::start($event);
        if (! $start) {
            return null;
        }
        $end = EventTime::parse($event['ends_at'] ?? null, $event['timezone'] ?? null);
        $endText = $end ? $end->format('H : i') : (($event['end_label'] ?? null) ?: 'Selesai');

        return 'Pukul '.$start->format('H : i').' - '.$endText;
    }

    /** Tanggal di cover: akad dulu, lalu resepsi. */
    public static function coverDate(array $payload): ?string
    {
        return self::date($payload['akad'] ?? []) ?? self::date($payload['resepsi'] ?? []);
    }

    /** ISO untuk countdown: acara terdekat yang belum lewat (akad dulu). */
    public static function countdownIso(array $payload): ?string
    {
        foreach (['akad', 'resepsi'] as $k) {
            if ($iso = EventTime::startsAtIso($payload[$k] ?? [])) {
                return $iso;
            }
        }

        return null;
    }
}
