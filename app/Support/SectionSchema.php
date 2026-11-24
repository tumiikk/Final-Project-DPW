<?php

namespace App\Support;

/**
 * Aturan validasi per section. Hanya field yang tercantum di sini yang diterima
 * (field lain dibuang oleh Validator::validated()), sehingga aman dari mass assignment.
 * Format aturan sama dengan Laravel dan diproses oleh App\Core\Validator.
 */
final class SectionSchema
{
    private const PATH_REGEX = '/^(?!.*\.\.)[A-Za-z0-9_\-\/\.]+$/';

    public static function rules(string $key): array
    {
        return match ($key) {
            'cover' => [
                'eyebrow' => self::s(60), 'bride_name' => self::s(60, true), 'groom_name' => self::s(60, true),
                'show_date' => self::b(), 'date_fallback' => self::s(120),
                'guest_label' => self::s(60), 'guest_fallback' => self::s(60),
                'button_text' => self::s(40, true),
            ] + self::m('background'),

            'intro' => [
                'title' => self::s(120, true), 'text' => self::s(300),
            ] + self::m('photo_top') + self::m('photo_bottom'),

            'quote' => [
                'text' => self::s(1200, true), 'source' => self::s(120), 'note' => self::s(300),
            ] + self::m('background'),

            'couple_intro' => [
                'title' => self::s(120, true), 'text' => self::s(300),
            ] + self::m('photo'),

            'couple' => [
                'title' => self::s(120, true), 'subtitle' => self::s(300),
            ] + self::m('background') + self::m('side_photo') + self::person('bride') + self::person('groom'),

            'happiest_day' => [
                'title' => self::s(120, true), 'subtitle' => self::s(200),
            ] + self::m('photo_top') + self::m('photo_bottom'),

            'akad', 'resepsi' => self::event(),

            'party' => [
                'title' => self::s(120, true), 'subtitle' => self::s(300),
            ] + self::m('background'),

            'gallery' => [
                'title' => self::s(120, true), 'description' => self::s(300), 'group_caption' => self::s(200),
                'items' => ['nullable', 'array', 'max:60'],
                'items.*' => ['array'],
                'items.*.id' => self::s(40),
                'items.*.caption' => self::s(200),
                'items.*.is_active' => self::b(),
            ] + self::m('items.*.photo', true),

            'story_intro' => [
                'title' => self::s(120, true), 'subtitle' => self::s(300),
            ] + self::m('photo'),

            'love_story' => [
                'title' => self::s(120, true), 'description' => self::s(300),
                'items' => ['nullable', 'array', 'max:30'],
                'items.*' => ['array'],
                'items.*.id' => self::s(40),
                'items.*.year' => self::s(20, true),
                'items.*.title' => self::s(120, true),
                'items.*.description' => self::s(600),
                'items.*.photos' => ['nullable', 'array', 'max:6'],
            ] + self::m('items.*.photos.*'),

            'rsvp' => [
                'title' => self::s(120, true), 'name_label' => self::s(60), 'attendance_label' => self::s(60),
                'attendance_choice_label' => self::s(60), 'attend_yes_label' => self::s(40),
                'attend_no_label' => self::s(40), 'count_label' => self::s(60), 'message_label' => self::s(60),
                'button_text' => self::s(40, true), 'wishes_title' => self::s(60),
                'wishes_visible_count' => ['required', 'integer', 'between:1,10'],
                'success_text' => self::s(200),
            ] + self::m('background'),

            'gift' => [
                'is_visible' => self::b(), 'title' => self::s(120, true), 'description' => self::s(600),
                'show_bank' => self::b(), 'bank_title' => self::s(60), 'copy_text' => self::s(40),
                'show_qris' => self::b(), 'qris_title' => self::s(60), 'qris_caption' => self::s(200),
                'accounts' => ['nullable', 'array', 'max:10'],
                'accounts.*' => ['array'],
                'accounts.*.id' => self::s(40),
                'accounts.*.recipient' => self::s(120, true),
                'accounts.*.bank_name' => self::s(80, true),
                'accounts.*.account_number' => ['required', 'string', 'max:40', 'regex:/^[0-9A-Za-z .\-\/+]+$/'],
                'accounts.*.note' => self::s(200),
                'accounts.*.is_active' => self::b(),
            ] + self::m('accounts.*.logo') + self::m('qris_image'),

            'dress_code' => [
                'is_visible' => self::b(), 'title' => self::s(120, true), 'description' => self::s(600),
                'note' => self::s(300), 'colors_title' => self::s(80),
                'men' => ['nullable', 'array'], 'men.label' => self::s(30), 'men.text' => self::s(200),
                'women' => ['nullable', 'array'], 'women.label' => self::s(30), 'women.text' => self::s(200),
                'colors' => ['nullable', 'array', 'max:8'],
                'colors.*' => ['array'],
                'colors.*.name' => self::s(40),
                'colors.*.hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ] + self::m('image') + self::m('men.photo') + self::m('women.photo'),

            'closing' => [
                'title' => self::s(120, true), 'text' => self::s(300),
            ] + self::m('photo') + self::m('background'),

            'music' => [
                'enabled' => self::b(), 'title' => self::s(120), 'artist' => self::s(120),
            ] + self::m('file'),

            default => throw new \InvalidArgumentException("Section tidak dikenal: $key"),
        };
    }

    private static function event(): array
    {
        return [
            'heading' => self::s(80), 'intro' => self::s(500), 'title' => self::s(80, true),
            'closing' => self::s(300),
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'end_label' => self::s(30),
            'timezone' => ['required', 'timezone:all'],
            'venue_name' => self::s(150), 'address' => self::s(400),
            // aturan GoogleMapsUrl ditambahkan di SectionRequest
            'maps_url' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'button_text' => self::s(40, true),
            'photos' => ['nullable', 'array', 'max:6'],
        ] + self::m('photos.*') + self::m('photo_main') + self::m('photo_side');
    }

    private static function person(string $who): array
    {
        return [
            "$who" => ['required', 'array'],
            "$who.nickname" => self::s(60, true),
            "$who.full_name" => self::s(120),
            "$who.child_label" => self::s(40),
            "$who.father_name" => self::s(120),
            "$who.mother_name" => self::s(120),
        ] + self::m("$who.photo");
    }

    private static function s(int $max, bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', 'string', "max:$max"];
    }

    private static function b(): array
    {
        return ['nullable', 'boolean'];
    }

    /** Aturan untuk satu referensi media (foto/audio). */
    private static function m(string $p, bool $required = false): array
    {
        return [
            $p => [$required ? 'required' : 'nullable', 'array', 'required_array_keys:source,path'],
            "$p.source" => ['string', 'in:default,upload'],
            "$p.path" => ['string', 'max:255', 'regex:'.self::PATH_REGEX],
            "$p.alt" => ['nullable', 'string', 'max:150'],
            "$p.crop" => ['nullable', 'array'],
            "$p.crop.x" => ['nullable', 'numeric', 'between:0,100'],
            "$p.crop.y" => ['nullable', 'numeric', 'between:0,100'],
            "$p.crop.zoom" => ['nullable', 'numeric', 'between:1,4'],
        ];
    }
}
