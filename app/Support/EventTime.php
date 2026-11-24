<?php

namespace App\Support;

/** Waktu acara disimpan sebagai teks lokal + timezone; countdown memakai ISO-8601 dengan offset. */
final class EventTime
{
    public const FORMAT = 'Y-m-d\TH:i';

    public static function parse(?string $local, ?string $timezone): ?\DateTimeImmutable
    {
        if ($local === null || $local === '') {
            return null;
        }

        try {
            $tz = new \DateTimeZone($timezone ?: DefaultContent::TIMEZONE);
        } catch (\Exception) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!'.self::FORMAT, $local, $tz);

        return $date ?: null;
    }

    /** Dipakai countdown. Null jika tanggal belum diisi (countdown disembunyikan). */
    public static function startsAtIso(array $event): ?string
    {
        return self::parse($event['starts_at'] ?? null, $event['timezone'] ?? null)?->format(DATE_ATOM);
    }
}
