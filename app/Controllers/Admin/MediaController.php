<?php

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\ValidationException;
use App\Services\MediaService;

/**
 * Upload foto & lagu dari editor. Upload hanya menyimpan file + mencatatnya;
 * pemakaian foto/lagu baru berlaku saat section disimpan ke draft.
 *
 * Validasi file (pengganti UploadImageRequest & UploadAudioRequest):
 * jenis file dicek dari ISI file (bukan nama/ekstensi), ukuran maksimum, dan dimensi foto.
 */
class MediaController
{
    public function image(): void
    {
        $c = config('invitation.uploads.image');
        $file = $this->uploadedFile();
        $mime = MediaService::detectMime($file['tmp_name']);
        $errors = [];
        // Ukuran minimal foto (grup "gift" boleh lebih kecil karena dipakai untuk logo bank)
        [$minW, $minH] = $c['min_by_group'][input('group')] ?? [$c['min_width'], $c['min_height']];

        // Urutan pemeriksaan sama dengan aturan Laravel: image -> mimes -> max -> dimensions
        if (! str_starts_with($mime, 'image/')) {
            $errors['file'] = 'The file field must be an image.';
        } elseif (MediaService::imageExtension($mime) === null) {
            $errors['file'] = 'Format foto harus JPG, PNG, atau WebP.';
        } elseif ($file['size'] / 1024 > $c['max_kb']) {
            $errors['file'] = 'Ukuran foto maksimal '.round($c['max_kb'] / 1024).' MB.';
        } else {
            $dims = @getimagesize($file['tmp_name']);
            if (! $dims || $dims[0] < $minW || $dims[1] < $minH || $dims[0] > $c['max_dimension'] || $dims[1] > $c['max_dimension']) {
                $errors['file'] = "Dimensi foto tidak sesuai (minimal {$minW}x{$minH} piksel, maksimal 8000 piksel).";
            }
        }

        $group = input('group');
        if ($group !== null && ! in_array($group, config('invitation.uploads.groups'), true)) {
            $errors['group'] = 'The selected group is invalid.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        json_response((new MediaService())->storeImage($file, $group ?? 'misc'), 201);
    }

    public function audio(): void
    {
        $c = config('invitation.uploads.audio');
        $file = $this->uploadedFile();
        $mime = MediaService::detectMime($file['tmp_name']);

        if (MediaService::audioExtension($mime) === null) {
            throw ValidationException::withMessages(['file' => 'Format lagu harus MP3, M4A, OGG, atau WAV.']);
        }
        if ($file['size'] / 1024 > $c['max_kb']) {
            throw ValidationException::withMessages(['file' => 'Ukuran lagu maksimal '.round($c['max_kb'] / 1024).' MB.']);
        }

        json_response((new MediaService())->storeAudio($file), 201);
    }

    /** Mengambil $_FILES['file'] dan memastikan upload benar-benar berhasil sampai server. */
    private function uploadedFile(): array
    {
        // Body melebihi post_max_size: PHP membuang seluruh isi request
        $max = $this->iniBytes((string) ini_get('post_max_size'));
        if ($max > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $max) {
            throw new HttpException(413, 'Ukuran file melebihi batas server (post_max_size).');
        }

        $file = $_FILES['file'] ?? null;
        if (! is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw ValidationException::withMessages(['file' => 'The file field is required.']);
        }
        if (is_array($file['name']) || $file['error'] !== UPLOAD_ERR_OK || ! is_uploaded_file($file['tmp_name'])) {
            throw ValidationException::withMessages(['file' => 'The file failed to upload.']);
        }

        return $file;
    }

    /** Mengubah nilai php.ini seperti "20M" menjadi byte. */
    private function iniBytes(string $v): int
    {
        $n = (int) $v;

        return match (strtoupper(substr(trim($v), -1))) {
            'G' => $n * 1024 ** 3,
            'M' => $n * 1024 ** 2,
            'K' => $n * 1024,
            default => $n,
        };
    }
}
