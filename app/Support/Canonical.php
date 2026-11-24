<?php

namespace App\Support;

/** Hash konten yang tidak terpengaruh urutan key, untuk membandingkan draft vs published. */
final class Canonical
{
    public static function hash(array $data): string
    {
        return sha1(json_encode(
            self::sort($data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ));
    }

    public static function sort(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($v) => self::sort($v), $value);
    }
}
