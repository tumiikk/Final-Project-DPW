<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Core\ValidationException;
use App\Services\ContentRepository;
use App\Services\InvitationPublishService;
use App\Support\AdminFields;
use App\Support\DefaultContent;
use App\Support\EventTime;
use App\Support\MapsLink;
use App\Support\SectionSchema;

/** Daftar section konten, halaman editor (form + Live Preview), dan simpan draft per section. */
class SectionController
{
    /** Daftar semua section beserta jumlah info penting yang belum diisi. */
    public function index(): void
    {
        $missing = (new ContentRepository())->missingInfo();

        $list = [];
        foreach (DefaultContent::labels() as $key => $label) {
            $list[] = [
                'key' => $key,
                'label' => $label,
                'missing' => array_values(array_filter($missing, fn ($path) => str_starts_with($path, "$key."), ARRAY_FILTER_USE_KEY)),
            ];
        }

        if (expects_json()) {
            json_response($list);
        } else {
            html_response(view('admin.sections', ['sections' => $list]));
        }
    }

    /** Halaman editor satu section (atau data JSON-nya untuk JavaScript). */
    public function show(string $section): void
    {
        $content = new ContentRepository();
        $draft = $content->ensureDraft();

        $data = [
            'key' => $section,
            'label' => DefaultContent::labels()[$section],
            'version' => $draft['version'],
            'data' => $content->normalize($draft['payload'])[$section],
            'default' => DefaultContent::section($section),
        ];

        if (expects_json()) {
            json_response($data);

            return;
        }

        html_response(view('admin.editor', $data + [
            'fields' => AdminFields::for($section),
            'library' => AdminFields::defaultLibrary(),
            'sections' => DefaultContent::labels(),
        ]));
    }

    /** Body JSON: { "data": { ...field section... }, "expected_version": 3 } */
    public function update(string $section): void
    {
        $rules = ['expected_version' => ['nullable', 'integer', 'min:1'], 'data' => ['required', 'array']];
        foreach (SectionSchema::rules($section) as $field => $rule) {
            $rules["data.$field"] = $rule;
        }

        // Link lokasi Akad/Resepsi hanya boleh tautan Google Maps (https)
        if (in_array($section, ['akad', 'resepsi'], true)) {
            $rules['data.maps_url'][] = function (string $attr, mixed $value, callable $fail) {
                if (! is_string($value) || ! MapsLink::isGoogleMapsUrl($value)) {
                    $fail('Link harus berupa tautan Google Maps (https://maps.app.goo.gl/..., https://www.google.com/maps/...).');
                }
            };
        }

        $validator = new Validator(request_input(), $rules);

        // Pemeriksaan lanjutan khusus acara (dijalankan hanya bila aturan dasar sudah lolos)
        if (! $validator->fails() && in_array($section, ['akad', 'resepsi'], true)) {
            $this->checkEvent($validator, (array) input('data', []));
        }
        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $valid = $validator->validated();
        $content = new ContentRepository();
        $draft = $content->saveSection(
            $section,
            $valid['data'] ?? [],
            isset($valid['expected_version']) ? (int) $valid['expected_version'] : null
        );

        json_response([
            'saved' => true,
            'version' => $draft['version'],
            'content' => (new InvitationPublishService())->status(),
        ]);
    }

    /** Waktu selesai tidak boleh sebelum waktu mulai; latitude & longitude harus diisi berpasangan. */
    private function checkEvent(Validator $validator, array $d): void
    {
        $start = EventTime::parse($d['starts_at'] ?? null, $d['timezone'] ?? null);
        $end = EventTime::parse($d['ends_at'] ?? null, $d['timezone'] ?? null);

        if ($end && ! $start) {
            $validator->addError('data.ends_at', 'Isi waktu mulai terlebih dahulu.');
        } elseif ($start && $end && $end < $start) {
            $validator->addError('data.ends_at', 'Waktu selesai tidak boleh sebelum waktu mulai.');
        }

        if (filled($d['latitude'] ?? null) !== filled($d['longitude'] ?? null)) {
            $validator->addError('data.latitude', 'Latitude dan longitude harus diisi bersamaan.');
        }
    }
}
