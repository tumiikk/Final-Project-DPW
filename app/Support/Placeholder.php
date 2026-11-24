<?php

namespace App\Support;

/**
 * Penanda "perlu diisi": nilai kosong, atau teks yang diapit tanda kurung siku,
 * contoh "[Nama Ayah]". Dipakai agar dashboard bisa menandai data yang belum final.
 */
final class Placeholder
{
    public static function is(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return true;
        }

        return is_string($value) && preg_match('/^\[.*\]$/su', trim($value)) === 1;
    }
}
