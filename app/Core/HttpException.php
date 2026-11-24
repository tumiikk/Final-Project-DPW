<?php

namespace App\Core;

/**
 * Error HTTP yang disengaja (404 tidak ditemukan, 403 dilarang, 409 konflik, 429 terlalu banyak percobaan, dst).
 * Ditangkap oleh public/index.php lalu diubah menjadi halaman error atau JSON.
 */
class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $status, string $message = '', public readonly array $headers = [])
    {
        parent::__construct($message);
    }
}
