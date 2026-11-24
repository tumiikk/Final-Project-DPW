<?php

/**
 * Menghapus file upload yang tidak dipakai draft maupun published
 * (pengganti `php artisan invitation:purge-media`).
 *
 *   php cli/purge-media.php         -> hanya file yang diunggah > 120 menit lalu
 *   php cli/purge-media.php --now   -> abaikan masa tenggang
 *
 * Pembersihan yang sama juga berjalan otomatis setiap Publish, Reset, dan Batalkan perubahan.
 */

use App\Services\MediaService;

require dirname(__DIR__).'/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit("Jalankan lewat terminal: php cli/purge-media.php\n");
}

$deleted = (new MediaService())->purgeOrphans(in_array('--now', $argv, true));
echo $deleted." file dihapus.\n";
