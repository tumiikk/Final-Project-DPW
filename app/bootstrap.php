<?php

/**
 * Bootstrap aplikasi: dimuat oleh public/index.php (web) dan file di folder cli/ (terminal).
 * Tugasnya: menentukan folder proyek, memuat helper & .env, mendaftarkan autoloader class,
 * dan mengatur zona waktu serta tampilan error.
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/app/helpers.php';

// Variabel dari file .env (lokal). Di Railway, variabel diambil langsung dari environment server.
load_env(BASE_PATH.'/.env');

// Autoloader: class App\Services\MediaService => app/Services/MediaService.php
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

mb_internal_encoding('UTF-8');
date_default_timezone_set((string) config('app.timezone'));

// Detail error hanya ditampilkan saat APP_DEBUG=true (lokal). Production cukup dicatat di log.
error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');

// Peringatan/notice PHP diubah menjadi exception agar tidak diam-diam menghasilkan tampilan rusak.
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (! (error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
