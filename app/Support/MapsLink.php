<?php

namespace App\Support;

/**
 * Link lokasi acara. Admin mengisi link Google Maps dari dashboard.
 * Hanya https dan host Google Maps yang diterima (mencegah javascript:, phishing, dsb).
 */
final class MapsLink
{
    private const GOOGLE_HOSTS = [
        'google.com', 'www.google.com', 'google.co.id', 'www.google.co.id', 'maps.google.com',
    ];

    public static function isGoogleMapsUrl(string $url): bool
    {
        $url = trim($url);
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }
        if (strtolower($parts['scheme']) !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';

        if ($host === 'maps.app.goo.gl') {
            return strlen($path) > 1;
        }
        if ($host === 'goo.gl') {
            return str_starts_with($path, '/maps');
        }
        if ($host === 'maps.google.com') {
            return true;
        }

        return in_array($host, self::GOOGLE_HOSTS, true) && str_starts_with($path, '/maps');
    }

    /**
     * Link yang dipakai di landing: link Google Maps dari admin; jika kosong,
     * koordinat; jika kosong juga, pencarian alamat. Null jika tidak ada data.
     */
    public static function resolve(?string $mapsUrl, mixed $lat, mixed $lng, ?string $address): ?string
    {
        if (! Placeholder::is($mapsUrl) && self::isGoogleMapsUrl((string) $mapsUrl)) {
            return trim((string) $mapsUrl);
        }

        if (is_numeric($lat) && is_numeric($lng)) {
            return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($lat.','.$lng);
        }

        if (! Placeholder::is($address)) {
            return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(trim((string) $address));
        }

        return null;
    }
}
