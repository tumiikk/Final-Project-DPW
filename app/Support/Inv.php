<?php

namespace App\Support;

/** Helper tampilan landing/preview. Placeholder "[...]" hanya tampil di preview dashboard, tidak di halaman publik. */
final class Inv
{
    public static function text(mixed $v, bool $preview = false): string
    {
        if (! is_string($v) || trim($v) === '') {
            return '';
        }

        return ($preview || ! Placeholder::is($v)) ? $v : '';
    }

    /** Gaya crop non-destruktif: titik fokus (x,y %) + zoom. */
    public static function cropStyle(?array $ref): string
    {
        $c = $ref['crop'] ?? null;
        if (! is_array($c)) {
            return '';
        }
        $x = (float) ($c['x'] ?? 50);
        $y = (float) ($c['y'] ?? 50);
        $z = max(1.0, (float) ($c['zoom'] ?? 1));

        return sprintf('--fx:%s%%;--fy:%s%%;--fz:%s', round($x, 2), round($y, 2), round($z, 3));
    }

    /** Pecah teks nama tamu aman untuk atribut. */
    public static function parents(array $p, bool $preview): string
    {
        $f = self::text($p['father_name'] ?? null, $preview);
        $m = self::text($p['mother_name'] ?? null, $preview);

        return trim($f.($f !== '' && $m !== '' ? ' & ' : '').$m);
    }

    /**
     * Isi kartu Kirim Hadiah yang benar-benar ditampilkan ke tamu:
     * - accounts : rekening yang aktif (bila kartu Transfer Bank diaktifkan)
     * - bank     : kartu Transfer Bank tampil (ada rekening aktif)
     * - qris     : kartu QRIS tampil (kartu diaktifkan dan gambar sudah diunggah)
     */
    public static function giftCards(array $g): array
    {
        $accounts = ($g['show_bank'] ?? true)
            ? array_values(array_filter($g['accounts'] ?? [], fn ($a) => is_array($a) && ($a['is_active'] ?? true)))
            : [];
        $qris = ($g['show_qris'] ?? true) && ! empty($g['qris_image']['path']);

        return ['accounts' => $accounts, 'bank' => $accounts !== [], 'qris' => $qris];
    }

    /** Section Kirim Hadiah tampil di undangan publik (dan menu HADIAH di navbar) bila diaktifkan dan ada isinya. */
    public static function giftVisible(array $payload): bool
    {
        $g = $payload['gift'] ?? [];
        $cards = self::giftCards($g);

        return ($g['is_visible'] ?? false) && ($cards['bank'] || $cards['qris']);
    }

    /** Section Dresscode tampil di undangan publik (dan di menu ACARA) bila diaktifkan admin. */
    public static function dressVisible(array $payload): bool
    {
        return (bool) ($payload['dress_code']['is_visible'] ?? false);
    }
}
