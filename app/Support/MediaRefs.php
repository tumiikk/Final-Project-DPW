<?php

namespace App\Support;

/**
 * Referensi media di payload berbentuk:
 *   ['source' => 'default'|'upload', 'path' => '...', 'crop' => ['x','y','zoom']|null, 'alt' => ...]
 * Crop bersifat non-destruktif: titik fokus (x,y dalam persen) + zoom, file asli tidak diubah.
 */
final class MediaRefs
{
    public static function collect(array $data): array
    {
        $out = [];
        self::walk($data, $out);

        return $out;
    }

    public static function uploadPaths(array $data): array
    {
        $paths = [];
        foreach (self::collect($data) as $ref) {
            if ($ref['source'] === 'upload') {
                $paths[] = $ref['path'];
            }
        }

        return array_values(array_unique($paths));
    }

    public static function safePath(string $path): bool
    {
        return $path !== ''
            && strlen($path) <= 255
            && ! str_contains($path, '..')
            && ! str_starts_with($path, '/')
            && preg_match('/^[A-Za-z0-9_\-\/\.]+$/', $path) === 1;
    }

    private static function walk(mixed $node, array &$out): void
    {
        if (! is_array($node)) {
            return;
        }

        if (isset($node['source'], $node['path']) && is_string($node['source']) && is_string($node['path'])) {
            $out[] = $node;

            return;
        }

        foreach ($node as $child) {
            self::walk($child, $out);
        }
    }
}
