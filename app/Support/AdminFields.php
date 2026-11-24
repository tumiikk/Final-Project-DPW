<?php

namespace App\Support;

/**
 * Deskriptor form editor per section (dikirim ke editor.js sebagai JSON).
 * Tipe: text, area, bool, num, dt, url, select, color, photo, photos, group, list, audio.
 * 'r' pada foto = rasio lebar/tinggi slot di desain, dipakai area crop agar hasilnya sama dengan landing.
 */
final class AdminFields
{
    private static function t(string $k, string $l, int $max = 120, array $o = []): array { return ['t' => 'text', 'k' => $k, 'l' => $l, 'max' => $max] + $o; }
    private static function a(string $k, string $l, int $max = 300, int $rows = 3, array $o = []): array { return ['t' => 'area', 'k' => $k, 'l' => $l, 'max' => $max, 'rows' => $rows] + $o; }
    private static function p(string $k, string $l, float $r, array $o = []): array { return ['t' => 'photo', 'k' => $k, 'l' => $l, 'r' => round($r, 4)] + $o; }
    private static function b(string $k, string $l, array $o = []): array { return ['t' => 'bool', 'k' => $k, 'l' => $l] + $o; }

    private static function event(bool $isAkad): array
    {
        $f = [];
        if ($isAkad) {
            $f[] = self::t('heading', 'Judul bagian atas', 80, ['hint' => 'Mockup: "Save the Date"']);
            $f[] = self::a('intro', 'Kalimat pengantar', 500, 4);
        }
        $f[] = self::t('title', $isAkad ? 'Nama acara (di kotak tanggal)' : 'Nama acara', 80, ['req' => true]);
        $f[] = ['t' => 'group', 'l' => 'Tanggal & waktu', 'k' => null, 'f' => [
            ['t' => 'dt', 'k' => 'starts_at', 'l' => 'Mulai', 'hint' => 'Dipakai untuk tanggal di undangan dan hitung mundur'],
            ['t' => 'dt', 'k' => 'ends_at', 'l' => 'Selesai (opsional)'],
            self::t('end_label', 'Teks bila waktu selesai kosong', 30, ['hint' => 'Contoh: Selesai']),
            ['t' => 'select', 'k' => 'timezone', 'l' => 'Zona waktu', 'opts' => [['Asia/Jakarta', 'WIB (Asia/Jakarta)'], ['Asia/Makassar', 'WITA (Asia/Makassar)'], ['Asia/Jayapura', 'WIT (Asia/Jayapura)']]],
        ]];
        $f[] = ['t' => 'group', 'l' => 'Lokasi', 'k' => null, 'f' => [
            self::t('venue_name', 'Nama tempat', 150),
            self::a('address', 'Alamat lengkap', 400, 3),
            ['t' => 'url', 'k' => 'maps_url', 'l' => 'Tautan Google Maps', 'max' => 500, 'hint' => 'Tempel tautan dari Google Maps (maps.app.goo.gl / google.com/maps)'],
            ['t' => 'num', 'k' => 'latitude', 'l' => 'Latitude (untuk peta tertanam)', 'step' => 'any', 'min' => -90, 'max' => 90],
            ['t' => 'num', 'k' => 'longitude', 'l' => 'Longitude', 'step' => 'any', 'min' => -180, 'max' => 180],
            self::t('button_text', 'Teks tombol lokasi', 40, ['req' => true]),
        ]];
        if ($isAkad) {
            $f[] = self::a('closing', 'Kalimat penutup', 300, 2);
            $f[] = ['t' => 'photos', 'k' => 'photos', 'l' => 'Foto kolase', 'fixed' => 4, 'slots' => [
                ['Foto kiri besar (tegak)', 243 / 425], ['Foto kanan atas', 240 / 152], ['Foto kanan bawah (tegak)', 241 / 390], ['Foto kiri bawah', 243 / 131],
            ]];
        } else {
            $f[] = self::p('photo_main', 'Foto utama (bingkai lengkung)', 392 / 503);
            $f[] = self::p('photo_side', 'Foto pendamping', 394 / 178);
        }

        return $f;
    }

    public static function for(string $section): array
    {
        return match ($section) {
            'cover' => [
                self::t('eyebrow', 'Tulisan atas', 60, ['hint' => 'Mockup: THE WEDDING OF']),
                self::t('bride_name', 'Nama mempelai wanita', 60, ['req' => true]),
                self::t('groom_name', 'Nama mempelai pria', 60, ['req' => true]),
                self::b('show_date', 'Tampilkan tanggal pernikahan', ['hint' => 'Tanggal otomatis dari acara Akad (atau Resepsi)']),
                self::t('date_fallback', 'Teks bila tanggal acara belum diisi', 120),
                self::t('guest_label', 'Teks sebelum nama tamu', 60),
                self::t('guest_fallback', 'Nama tamu cadangan (tanpa link personal)', 60),
                self::t('button_text', 'Teks tombol', 40, ['req' => true]),
                self::p('background', 'Foto latar cover', 16 / 9),
            ],
            'intro' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('text', 'Kalimat pengantar', 300),
                self::p('photo_top', 'Foto kiri atas', 324 / 117),
                self::p('photo_bottom', 'Foto kanan bawah', 445 / 133),
            ],
            'quote' => [
                self::a('text', 'Teks ayat / kutipan', 1200, 8, ['req' => true]),
                self::t('source', 'Sumber kutipan', 120, ['hint' => 'Ditampilkan sebagai — (sumber) —']),
                self::t('note', 'Keterangan tambahan (opsional)', 300),
                self::p('background', 'Foto latar', 16 / 9),
            ],
            'couple_intro' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('text', 'Kalimat pengantar', 300),
                self::p('photo', 'Foto', 394 / 178),
            ],
            'couple' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('subtitle', 'Kalimat pengantar', 300),
                self::p('background', 'Foto banner atas', 1440 / 333),
                self::p('side_photo', 'Foto panel kiri (bagian mempelai pria)', 420 / 675),
                ['t' => 'group', 'l' => 'Mempelai wanita', 'k' => 'bride', 'f' => self::person(251 / 341)],
                ['t' => 'group', 'l' => 'Mempelai pria', 'k' => 'groom', 'f' => self::person(242 / 357)],
            ],
            'happiest_day' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::t('subtitle', 'Subjudul', 200),
                self::p('photo_top', 'Foto kiri atas', 409 / 159),
                self::p('photo_bottom', 'Foto kanan bawah', 394 / 178),
            ],
            'akad' => self::event(true),
            'resepsi' => self::event(false),
            'party' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('subtitle', 'Kalimat pengantar', 300),
                self::p('background', 'Foto latar', 1440 / 412),
                ['t' => 'note', 'l' => 'Hitung mundur tampil di bagian ini otomatis dari tanggal Akad/Resepsi.'],
            ],
            'gallery' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('description', 'Deskripsi', 300, 2),
                self::t('group_caption', 'Keterangan kelompok foto', 200, ['hint' => 'Mockup: "Yang sudah kami lalui bersama"']),
                ['t' => 'list', 'k' => 'items', 'l' => 'Foto galeri', 'item' => 'Foto', 'max' => 60, 'kind' => 'gallery',
                    'new' => ['id' => null, 'photo' => null, 'caption' => null, 'is_active' => true],
                    'f' => [self::p('photo', 'Foto', 4 / 3, ['req' => true]), self::t('caption', 'Caption', 200), self::b('is_active', 'Tampilkan di undangan')],
                    'note' => 'Sembilan foto pertama yang aktif mengisi kolase sesuai mockup; sisanya tampil sebagai baris tambahan di bawahnya.'],
            ],
            'story_intro' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::t('subtitle', 'Subjudul', 300),
                self::p('photo', 'Foto oval', 208 / 236),
            ],
            'love_story' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('description', 'Deskripsi', 300, 2, ['hint' => 'Kata "pasangan hidup." otomatis dicetak miring']),
                ['t' => 'list', 'k' => 'items', 'l' => 'Perjalanan kami', 'item' => 'Cerita', 'max' => 30, 'kind' => 'story',
                    'new' => ['id' => null, 'year' => '', 'title' => '', 'description' => null, 'photos' => []],
                    'f' => [self::t('year', 'Tahun', 20, ['req' => true]), self::t('title', 'Judul cerita', 120, ['req' => true]), self::a('description', 'Deskripsi', 600, 3),
                        ['t' => 'photos', 'k' => 'photos', 'l' => 'Foto cerita ini', 'max' => 6, 'r' => 4 / 3]]],
            ],
            'rsvp' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                ['t' => 'group', 'l' => 'Teks pada form', 'k' => null, 'f' => [
                    self::t('name_label', 'Label nama', 60), self::t('attendance_label', 'Label konfirmasi kehadiran', 60),
                    self::t('attendance_choice_label', 'Teks pilihan kehadiran', 60), self::t('attend_yes_label', 'Pilihan hadir', 40),
                    self::t('attend_no_label', 'Pilihan tidak hadir', 40), self::t('count_label', 'Label jumlah hadir', 60),
                    self::t('message_label', 'Label ucapan', 60), self::t('button_text', 'Teks tombol', 40, ['req' => true]),
                    self::a('success_text', 'Pesan setelah berhasil mengirim', 200, 2),
                ]],
                ['t' => 'group', 'l' => 'Kotak ucapan', 'k' => null, 'f' => [
                    self::t('wishes_title', 'Label kotak ucapan kosong', 60),
                    ['t' => 'num', 'k' => 'wishes_visible_count', 'l' => 'Jumlah ucapan yang terlihat sebelum digulir', 'min' => 1, 'max' => 10, 'int' => true, 'req' => true,
                        'hint' => 'Menentukan tinggi kotak ucapan. Semua ucapan tetap dapat dilihat dengan menggulir (scroll) di dalam kotak.'],
                ]],
                self::p('background', 'Foto latar', 16 / 9),
            ],
            // Kirim Hadiah: kartu Transfer Bank (kiri/atas) dan kartu QRIS (kanan/bawah)
            'gift' => [
                self::b('is_visible', 'Tampilkan bagian Kirim Hadiah di undangan', ['hint' => 'Tampil setelah Doa & Ucapan, hanya bila ada rekening aktif atau gambar QRIS. Menu HADIAH di navbar ikut tampil/tersembunyi.']),
                self::t('title', 'Judul section', 120, ['req' => true]),
                self::a('description', 'Kalimat pembuka', 600, 3),
                ['t' => 'group', 'l' => 'Kartu Transfer Bank', 'k' => null, 'f' => [
                    self::b('show_bank', 'Tampilkan kartu Transfer Bank'),
                    self::t('bank_title', 'Judul kartu', 60),
                    self::t('copy_text', 'Teks tombol salin', 40, ['hint' => 'Contoh: Salin Nomor Rekening']),
                    ['t' => 'list', 'k' => 'accounts', 'l' => 'Rekening bank', 'item' => 'Rekening', 'max' => 10, 'kind' => 'plain',
                        'new' => ['id' => null, 'bank_name' => '', 'account_number' => '', 'recipient' => '', 'note' => null, 'logo' => null, 'is_active' => true],
                        'f' => [self::t('bank_name', 'Nama bank', 80, ['req' => true]), self::t('account_number', 'Nomor rekening', 40, ['req' => true]),
                            self::t('recipient', 'Nama pemilik rekening', 120, ['req' => true]), self::t('note', 'Informasi tambahan (opsional)', 200),
                            self::p('logo', 'Logo bank (opsional)', 3, ['upload_only' => true, 'fit' => 'contain', 'hint' => 'Bila kosong, nama bank ditampilkan sebagai teks. Disarankan PNG berlatar transparan.']),
                            self::b('is_active', 'Tampilkan rekening ini')],
                        'note' => 'Urutan rekening di undangan mengikuti urutan daftar ini.'],
                ]],
                ['t' => 'group', 'l' => 'Kartu QRIS', 'k' => null, 'f' => [
                    self::b('show_qris', 'Tampilkan kartu QRIS'),
                    self::t('qris_title', 'Judul kartu', 60),
                    self::p('qris_image', 'Gambar QRIS', 1, ['upload_only' => true, 'fit' => 'contain', 'hint' => 'Unggah gambar QRIS yang jelas (JPG/PNG/WebP). Gunakan tombol Hapus untuk menghapusnya.']),
                    self::a('qris_caption', 'Keterangan di bawah QRIS', 200, 2),
                ]],
            ],
            // Dresscode: tampil setelah Resepsi
            'dress_code' => [
                self::b('is_visible', 'Tampilkan bagian Dresscode di undangan', ['hint' => 'Tampil setelah Resepsi dan menjadi pintasan di menu ACARA pada navbar.']),
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('description', 'Kalimat pengantar', 600, 3),
                ['t' => 'group', 'l' => 'Pakaian pria', 'k' => 'men', 'f' => [
                    self::t('label', 'Label', 30), self::p('photo', 'Foto contoh pakaian pria', 3 / 4, ['upload_only' => true]), self::a('text', 'Keterangan (opsional)', 200, 2),
                ]],
                ['t' => 'group', 'l' => 'Pakaian wanita', 'k' => 'women', 'f' => [
                    self::t('label', 'Label', 30), self::p('photo', 'Foto contoh pakaian wanita', 3 / 4, ['upload_only' => true]), self::a('text', 'Keterangan (opsional)', 200, 2),
                ]],
                self::t('colors_title', 'Judul warna', 80),
                ['t' => 'list', 'k' => 'colors', 'l' => 'Warna yang disarankan', 'item' => 'Warna', 'max' => 8, 'kind' => 'plain',
                    'new' => ['name' => '', 'hex' => '#2e486d'],
                    'f' => [self::t('name', 'Nama warna', 40), ['t' => 'color', 'k' => 'hex', 'l' => 'Warna']]],
                self::a('note', 'Keterangan tambahan', 300, 2),
                self::p('image', 'Gambar dresscode lengkap (opsional)', 3 / 4, ['upload_only' => true, 'fit' => 'contain', 'hint' => 'Bila Anda punya poster/gambar dresscode utuh, unggah di sini; tampil di bawah daftar warna.']),
            ],
            'closing' => [
                self::t('title', 'Judul', 120, ['req' => true]),
                self::a('text', 'Kalimat terima kasih', 300),
                self::p('photo', 'Foto oval', 206 / 272),
                self::p('background', 'Foto latar bawah', 1440 / 397),
            ],
            'music' => [
                self::b('enabled', 'Aktifkan musik latar', ['hint' => 'Musik hanya diputar setelah tamu menekan tombol buka undangan / tombol musik.']),
                self::t('title', 'Judul lagu', 120),
                self::t('artist', 'Artis', 120),
                ['t' => 'audio', 'k' => 'file', 'l' => 'File lagu (mp3, m4a, ogg, wav; maks 15 MB)'],
            ],
            default => throw new \InvalidArgumentException("Section tidak dikenal: $section"),
        };
    }

    private static function person(float $ratio): array
    {
        return [
            self::t('nickname', 'Nama panggilan (tampil besar)', 60, ['req' => true]),
            self::t('full_name', 'Nama lengkap', 120),
            self::t('child_label', 'Keterangan', 40, ['hint' => 'Mockup: PUTRI DARI / PUTRA DARI']),
            self::t('father_name', 'Nama ayah', 120),
            self::t('mother_name', 'Nama ibu', 120),
            self::p('photo', 'Foto', $ratio),
        ];
    }

    /** Daftar foto default yang tersedia (untuk "Pilih foto default"). */
    public static function defaultLibrary(): array
    {
        $root = public_path('images/default');
        $out = [];
        // Tanpa GLOB_BRACE agar berjalan di semua sistem operasi (Windows/Laragon, Linux/Railway)
        foreach (glob($root.'/*/*') ?: [] as $f) {
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $out[] = str_replace('\\', '/', substr($f, strlen($root) + 1));
            }
        }
        sort($out);

        return $out;
    }
}
