<?php

namespace App\Core;

/**
 * Dilempar saat input tidak lolos validasi.
 * - Request dari JavaScript  : dijawab JSON 422 { message, errors } (format sama dengan Laravel).
 * - Form HTML biasa (login)   : diarahkan kembali ke halaman sebelumnya beserta pesan error & input lama.
 */
class ValidationException extends \RuntimeException
{
    /** @param array<string, string[]> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(self::summary($errors));
    }

    /** Membuat exception dari pesan sederhana: ['field' => 'pesan']. */
    public static function withMessages(array $messages): self
    {
        return new self(array_map(fn ($m) => (array) $m, $messages));
    }

    /** Ringkasan seperti Laravel: "Pesan pertama (and 2 more errors)". */
    private static function summary(array $errors): string
    {
        $messages = array_merge(...array_values(array_map('array_values', $errors ?: [[]])));
        $first = $messages[0] ?? 'The given data was invalid.';
        $more = count($messages) - 1;

        return $more > 0 ? $first.' (and '.$more.' more '.($more === 1 ? 'error' : 'errors').')' : $first;
    }
}
