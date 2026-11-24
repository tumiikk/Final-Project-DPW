<?php

namespace App\Services;

use App\Models\InvitationSnapshot;
use App\Models\MediaFile;
use App\Support\MediaRefs;

/**
 * Upload foto & lagu, URL media, dan pembersihan file upload yang tidak terpakai.
 *
 * Lokasi file upload: public/storage/invitations/<grup>/<ULID>.<ext>
 * URL publiknya    : /storage/invitations/<grup>/<ULID>.<ext>
 * (sama dengan URL versi Laravel setelah `php artisan storage:link`, sehingga data lama tetap terbaca).
 */
final class MediaService
{
    /** Tipe MIME (dibaca dari ISI file, bukan nama file) yang diterima => ekstensi yang disimpan. */
    private const IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private const AUDIO_MIMES = [
        'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/x-mpeg' => 'mp3',
        'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/m4a' => 'm4a', 'video/mp4' => 'm4a',
        'audio/ogg' => 'ogg', 'application/ogg' => 'ogg', 'video/ogg' => 'ogg',
        'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/wave' => 'wav', 'audio/vnd.wave' => 'wav',
    ];

    /** Folder fisik penyimpanan upload. */
    public static function root(): string
    {
        return rtrim((string) config('invitation.uploads.root'), '/\\');
    }

    /** Tipe MIME asli file berdasarkan isinya (fileinfo). */
    public static function detectMime(string $tmpPath): string
    {
        return (string) (new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
    }

    /** Ekstensi untuk tipe MIME foto, atau null bila bukan JPG/PNG/WebP. */
    public static function imageExtension(string $mime): ?string
    {
        return self::IMAGE_MIMES[$mime] ?? null;
    }

    /** Ekstensi untuk tipe MIME audio, atau null bila bukan MP3/M4A/OGG/WAV. */
    public static function audioExtension(string $mime): ?string
    {
        return self::AUDIO_MIMES[$mime] ?? null;
    }

    /**
     * Menyimpan foto yang sudah lolos validasi lalu mencatatnya di media_files.
     * @param array $file elemen $_FILES['file']
     * @return array{ref: array, url: string, id: int}
     */
    public function storeImage(array $file, string $group = 'misc'): array
    {
        $group = in_array($group, config('invitation.uploads.groups'), true) ? $group : 'misc';
        $mime = self::detectMime($file['tmp_name']);
        $dims = @getimagesize($file['tmp_name']) ?: [null, null];

        $path = $this->move($file, config('invitation.uploads.dir')."/$group", self::imageExtension($mime) ?? 'jpg');

        $id = MediaFile::create([
            'kind' => 'image', 'disk' => config('invitation.uploads.disk'), 'path' => $path,
            'original_name' => mb_substr((string) $file['name'], 0, 200), 'mime' => $mime, 'size' => (int) $file['size'],
            'width' => $dims[0], 'height' => $dims[1],
        ]);

        return ['ref' => $this->uploadRef($path), 'url' => $this->url($this->uploadRef($path)), 'id' => $id];
    }

    /** @return array{ref: array, url: string, id: int} */
    public function storeAudio(array $file): array
    {
        $mime = self::detectMime($file['tmp_name']);
        $path = $this->move($file, config('invitation.uploads.dir').'/audio', self::audioExtension($mime) ?? 'mp3');

        $id = MediaFile::create([
            'kind' => 'audio', 'disk' => config('invitation.uploads.disk'), 'path' => $path,
            'original_name' => mb_substr((string) $file['name'], 0, 200), 'mime' => $mime, 'size' => (int) $file['size'],
        ]);

        return ['ref' => $this->uploadRef($path), 'url' => $this->url($this->uploadRef($path), 'audio'), 'id' => $id];
    }

    /** URL untuk landing/preview. File default yang belum ada diganti placeholder.svg. */
    public function url(?array $ref, string $kind = 'image'): ?string
    {
        if (! $ref || empty($ref['path'])) {
            return $kind === 'image' ? asset('images/default/placeholder.svg') : null;
        }

        if (($ref['source'] ?? null) === 'upload') {
            return url('storage/'.$ref['path']);
        }

        $folder = $kind === 'audio' ? 'audio/default' : 'images/default';

        if (MediaRefs::safePath($ref['path']) && is_file(public_path("$folder/{$ref['path']}"))) {
            return asset("$folder/{$ref['path']}");
        }

        return $kind === 'image' ? asset('images/default/placeholder.svg') : null;
    }

    /**
     * Hapus file upload yang tidak dirujuk draft maupun published dan sudah lewat masa tenggang
     * (agar upload yang masih di editor tidak ikut terhapus). Mengembalikan jumlah file dihapus.
     */
    public function purgeOrphans(bool $ignoreGrace = false): int
    {
        $referenced = [];
        foreach (InvitationSnapshot::allPayloads() as $payload) {
            foreach (MediaRefs::uploadPaths($payload) as $path) {
                $referenced[$path] = true;
            }
        }

        $before = $ignoreGrace ? null : date('Y-m-d H:i:s', time() - 60 * (int) config('invitation.uploads.orphan_grace_minutes'));

        $deleted = 0;
        foreach (MediaFile::createdBefore($before) as $media) {
            if (isset($referenced[$media['path']])) {
                continue;
            }
            try {
                $file = self::root().'/'.$media['path'];
                if (MediaRefs::safePath($media['path']) && is_file($file)) {
                    unlink($file);
                }
                MediaFile::delete((int) $media['id']);
                $deleted++;
            } catch (\Throwable $e) {
                log_error('Gagal menghapus file yatim', ['path' => $media['path'], 'error' => $e->getMessage()]);
            }
        }

        return $deleted;
    }

    public function uploadRef(string $path): array
    {
        return ['source' => 'upload', 'path' => $path, 'crop' => null, 'alt' => null];
    }

    /** Memindahkan file sementara upload ke folder tujuan dengan nama acak (ULID). Mengembalikan path relatif. */
    private function move(array $file, string $dir, string $ext): string
    {
        $target = self::root().'/'.$dir;
        if (! is_dir($target) && ! mkdir($target, 0775, true) && ! is_dir($target)) {
            throw new \RuntimeException('Folder upload tidak dapat dibuat: '.$target);
        }

        $path = $dir.'/'.ulid().'.'.$ext;
        if (is_uploaded_file($file['tmp_name'])) {
            $ok = move_uploaded_file($file['tmp_name'], self::root().'/'.$path);
        } else {
            // Hanya untuk skrip terminal (pengujian); request web wajib berupa upload HTTP asli
            $ok = PHP_SAPI === 'cli' && rename($file['tmp_name'], self::root().'/'.$path);
        }

        if (! $ok) {
            throw new \RuntimeException('File upload gagal disimpan. Periksa izin tulis folder '.$target);
        }

        return $path;
    }
}
