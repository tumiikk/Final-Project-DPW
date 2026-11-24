<?php

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\ValidationException;
use App\Models\InvitationSnapshot;
use App\Models\MediaFile;
use App\Support\Canonical;
use App\Support\DefaultContent;
use App\Support\MediaRefs;
use App\Support\Placeholder;

/**
 * Satu-satunya pintu baca/tulis konten undangan.
 * Editor menulis ke DRAFT; landing publik membaca PUBLISHED.
 */
final class ContentRepository
{
    /** Membuat draft dari Default Content jika belum ada. TIDAK PERNAH menimpa draft yang ada. */
    public function ensureDraft(): array
    {
        if ($row = InvitationSnapshot::find(InvitationSnapshot::DRAFT)) {
            return $row;
        }

        $payload = DefaultContent::all();
        $row = ['id' => null, 'status' => InvitationSnapshot::DRAFT, 'payload' => $payload, 'content_hash' => Canonical::hash($payload), 'version' => 1];

        try {
            InvitationSnapshot::save($row);

            return $row;
        } catch (\PDOException $e) {
            // Request lain membuat draft di saat yang sama (kolom status UNIQUE): pakai yang sudah ada
            if (Database::isDuplicate($e)) {
                return InvitationSnapshot::find(InvitationSnapshot::DRAFT);
            }
            throw $e;
        }
    }

    public function draft(): array
    {
        return $this->normalize($this->ensureDraft()['payload']);
    }

    public function published(): ?array
    {
        $row = InvitationSnapshot::find(InvitationSnapshot::PUBLISHED);

        return $row ? $this->normalize($row['payload']) : null;
    }

    /** Section baru di DefaultContent otomatis muncul pada snapshot lama. */
    public function normalize(array $payload): array
    {
        $defaults = DefaultContent::all();
        $lists = DefaultContent::listPaths();
        $result = array_replace($defaults, $payload);

        // Field baru di dalam section lama (mis. cover.guest_fallback) juga ikut terisi default.
        foreach ($defaults as $key => $default) {
            if (is_array($default) && isset($payload[$key]) && is_array($payload[$key])) {
                $result[$key] = $this->fillMissing($default, $payload[$key], $lists[$key] ?? null);
            }
        }

        return $result;
    }

    /** Melengkapi key yang hilang dari default, tanpa menimpa nilai yang sudah ada. Daftar item tidak disentuh. */
    private function fillMissing(array $defaults, array $data, ?string $listKey): array
    {
        foreach ($defaults as $k => $v) {
            if ($k === $listKey) {
                if (! array_key_exists($k, $data)) {
                    $data[$k] = $v;
                }
                continue;
            }

            if (! array_key_exists($k, $data)) {
                $data[$k] = $v;
            } elseif (is_array($v) && is_array($data[$k]) && ! array_is_list($v)) {
                $data[$k] = $this->fillMissing($v, $data[$k], null);
            }
        }

        return $data;
    }

    /**
     * Menyimpan satu section ke draft.
     * $expectedVersion = versi draft saat halaman editor dibuka; bila berbeda berarti draft sudah
     * diubah dari tab/perangkat lain, maka penyimpanan ditolak (409) agar perubahan orang lain tidak tertimpa.
     */
    public function saveSection(string $key, array $data, ?int $expectedVersion = null): array
    {
        $this->assertKnown($key);
        $data = $this->prepareSection($key, $data);
        $this->assertMedia($key, $data);
        $this->ensureDraft();

        return Database::transaction(function () use ($key, $data, $expectedVersion) {
            // Kunci baris draft selama transaksi
            $draft = InvitationSnapshot::find(InvitationSnapshot::DRAFT, true);

            if ($expectedVersion !== null && $draft['version'] !== $expectedVersion) {
                throw new HttpException(409, 'Draft sudah diubah dari tab/perangkat lain. Muat ulang halaman sebelum menyimpan.');
            }

            $payload = $this->normalize($draft['payload']);
            $payload[$key] = $data;
            $this->write($draft, $payload);

            return $draft;
        });
    }

    /** Dipakai reset: menimpa satu section draft dengan nilai default (tanpa validasi ulang). */
    public function replaceDraftSection(string $key, array $data): array
    {
        $this->assertKnown($key);
        $this->ensureDraft();

        $draft = InvitationSnapshot::find(InvitationSnapshot::DRAFT, true);
        $payload = $this->normalize($draft['payload']);
        $payload[$key] = $data;
        $this->write($draft, $payload);

        return $draft;
    }

    public function replaceDraftAll(array $payload): array
    {
        $this->ensureDraft();

        $draft = InvitationSnapshot::find(InvitationSnapshot::DRAFT, true);
        $this->write($draft, $payload);

        return $draft;
    }

    /** Info penting yang masih kosong / berupa placeholder (path => label). */
    public function missingInfo(?array $payload = null): array
    {
        $payload ??= $this->draft();
        $missing = [];

        foreach (DefaultContent::requiredInfo() as $path => $label) {
            if (Placeholder::is(data_get($payload, $path))) {
                $missing[$path] = $label;
            }
        }

        return $missing;
    }

    /** Menulis payload baru: hash konten dihitung ulang dan nomor versi bertambah. */
    public function write(array &$snapshot, array $payload): void
    {
        $snapshot['payload'] = $payload;
        $snapshot['content_hash'] = Canonical::hash($payload);
        $snapshot['version'] = ($snapshot['version'] ?? 0) + 1;
        InvitationSnapshot::save($snapshot);
    }

    private function assertKnown(string $key): void
    {
        if (! in_array($key, DefaultContent::sections(), true)) {
            throw new \InvalidArgumentException("Section tidak dikenal: $key");
        }
    }

    /** Memberi id pada item baru, memastikan id unik, dan is_active default true. */
    private function prepareSection(string $key, array $data): array
    {
        $listKey = DefaultContent::listPaths()[$key] ?? null;

        if ($listKey !== null) {
            $seen = [];
            $items = [];
            foreach (array_values($data[$listKey] ?? []) as $item) {
                if (empty($item['id']) || isset($seen[$item['id']])) {
                    $item['id'] = ulid();
                }
                $seen[$item['id']] = true;
                if (array_key_exists('is_active', $item) || in_array($key, ['gallery', 'gift'], true)) {
                    $item['is_active'] = (bool) ($item['is_active'] ?? true);
                }
                $items[] = $item;
            }
            $data[$listKey] = $items;
        }

        // Bool dari form yang tidak dikirim dianggap false
        foreach (['show_date', 'enabled', 'is_visible', 'show_bank', 'show_qris'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $data[$flag] = (bool) $data[$flag];
            }
        }

        return $data;
    }

    /** Referensi upload harus milik media_files dan bertipe sesuai (audio hanya untuk lagu). */
    private function assertMedia(string $key, array $data): void
    {
        $refs = MediaRefs::collect($data);
        $errors = [];

        foreach ($refs as $ref) {
            if (! MediaRefs::safePath($ref['path'])) {
                $errors['media'] = 'Path media tidak valid.';
            }
        }

        $uploadPaths = MediaRefs::uploadPaths($data);
        if ($uploadPaths !== []) {
            $known = MediaFile::kindsByPath($uploadPaths);
            $expectedKind = $key === 'music' ? 'audio' : 'image';

            foreach ($uploadPaths as $path) {
                if (! isset($known[$path])) {
                    $errors['media'] = 'File yang dipilih tidak ditemukan. Unggah ulang file tersebut.';
                } elseif ($known[$path] !== $expectedKind) {
                    $errors['media'] = $expectedKind === 'audio'
                        ? 'Lagu latar harus berupa file audio.'
                        : 'File yang dipilih bukan foto.';
                }
            }
        }

        if ($key === 'music' && isset($data['file']) && ($data['file']['source'] ?? null) === 'default') {
            $errors['media'] = 'Lagu latar harus diunggah dari dashboard.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
