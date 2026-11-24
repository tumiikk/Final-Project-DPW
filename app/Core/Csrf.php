<?php

namespace App\Core;

/**
 * Proteksi CSRF (pengganti middleware VerifyCsrfToken Laravel).
 * Setiap request yang mengubah data (POST/PUT/PATCH/DELETE) wajib membawa token session,
 * lewat input tersembunyi "_token" (form HTML) atau header "X-CSRF-TOKEN" (fetch JavaScript).
 */
final class Csrf
{
    public static function verify(): void
    {
        if (in_array(request_method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $sent = $_POST['_token'] ?? request_header('X-CSRF-TOKEN') ?? '';

        // hash_equals: perbandingan aman dari serangan timing
        if (! is_string($sent) || $sent === '' || ! hash_equals(csrf_token(), $sent)) {
            throw new HttpException(419, 'CSRF token mismatch.');
        }
    }
}
