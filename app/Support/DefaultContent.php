<?php

namespace App\Support;

/**
 * DEFAULT CONTENT (read-only). Sumber asli konten Ayu & Kevin.
 * Tidak disimpan di database dan tidak pernah ditimpa oleh pengeditan admin.
 *
 * - Teks mengikuti mockup. Data yang belum final memakai placeholder "[...]".
 * - Tanggal, jam, lokasi, dan alamat TIDAK diambil dari mockup: dibiarkan kosong/placeholder.
 * - Foto default menunjuk ke public/images/default/<path>. Jika file belum ada,
 *   MediaService menampilkan placeholder.svg.
 */
final class DefaultContent
{
    public const TIMEZONE = 'Asia/Jakarta';

    /** Urutan dan label section di dashboard (mengikuti urutan mockup). */
    public static function labels(): array
    {
        return [
            'cover' => 'Cover Undangan',
            'intro' => 'Introduction',
            'quote' => 'Kutipan / Ayat',
            'couple_intro' => 'Pengantar Mempelai',
            'couple' => 'Bride & Groom',
            'happiest_day' => 'The Happiest Day',
            'akad' => 'Akad Nikah',
            'party' => 'Prepare For The Party',
            'resepsi' => 'Resepsi',
            'dress_code' => 'Dresscode',
            'gallery' => 'Our Moments / Gallery',
            'story_intro' => 'Let See How This Story Begins',
            'love_story' => 'Love Story',
            'rsvp' => 'Pray For Us (RSVP & Ucapan)',
            'gift' => 'Kirim Hadiah',
            'closing' => 'Penutup',
            'music' => 'Lagu Latar',
        ];
    }

    public static function sections(): array
    {
        return array_keys(self::labels());
    }

    /** Section yang berisi daftar item (diberi id otomatis; urutan = urutan array). */
    public static function listPaths(): array
    {
        return ['gallery' => 'items', 'love_story' => 'items', 'gift' => 'accounts'];
    }

    /** Info penting yang harus diisi admin. path => label */
    public static function requiredInfo(): array
    {
        $info = [
            'couple.bride.full_name' => 'Nama lengkap mempelai wanita',
            'couple.bride.father_name' => 'Nama ayah mempelai wanita',
            'couple.bride.mother_name' => 'Nama ibu mempelai wanita',
            'couple.groom.full_name' => 'Nama lengkap mempelai pria',
            'couple.groom.father_name' => 'Nama ayah mempelai pria',
            'couple.groom.mother_name' => 'Nama ibu mempelai pria',
            'music.file' => 'File lagu latar (opsional)',
        ];

        foreach (['akad' => 'Akad Nikah', 'resepsi' => 'Resepsi'] as $key => $label) {
            $info["$key.starts_at"] = "$label: tanggal & jam mulai";
            $info["$key.venue_name"] = "$label: nama tempat";
            $info["$key.address"] = "$label: alamat lengkap";
            $info["$key.maps_url"] = "$label: link Google Maps";
        }

        return $info;
    }

    public static function img(string $path, ?string $alt = null): array
    {
        return ['source' => 'default', 'path' => $path, 'crop' => null, 'alt' => $alt];
    }

    public static function section(string $key): array
    {
        return self::all()[$key] ?? throw new \InvalidArgumentException("Section tidak dikenal: $key");
    }

    public static function all(): array
    {
        $i = fn (string $p, ?string $alt = null) => self::img($p, $alt);

        return [
            'cover' => [
                'eyebrow' => 'THE WEDDING OF',
                'bride_name' => 'Ayu',
                'groom_name' => 'Kevin',
                'show_date' => true,
                'date_fallback' => 'Tanggal akan segera diumumkan',
                'guest_label' => 'Kepada Yth.',
                'guest_fallback' => 'Tamu Undangan',
                'button_text' => 'BUKA UNDANGAN',
                'background' => $i('cover/background.jpg', 'Foto cover'),
            ],

            'intro' => [
                'title' => 'A little note of love',
                'text' => '“Sebuah kisah, sebuah janji, selamanya.”',
                'photo_top' => $i('intro/photo-top.jpg'),
                'photo_bottom' => $i('intro/photo-bottom.jpg'),
            ],

            'quote' => [
                'text' => 'Dan di antara tanda-tanda kekuasaan-Nya diciptakan-Nya untukmu pasangan hidup dari jenismu sendiri supaya kamu dapat ketenangan hati dan dijadikannya kasih sayang di antara kamu. Sesungguhnya yang demikian menjadi tanda-tanda kebesaran-Nya bagi orang-orang yang berpikir.',
                'source' => 'QS. Ar-Rum: 21',
                'note' => null,
                'background' => $i('quote/background.jpg'),
            ],

            'couple_intro' => [
                'title' => 'Yang Berbahagia',
                'text' => '“Semoga kita dalam Lindungan NYA.”',
                'photo' => $i('couple/intro-photo.jpg'),
            ],

            'couple' => [
                'title' => 'Bride & Groom',
                'subtitle' => 'Maha Suci Allah yang telah menciptakan makhluk-Nya berpasang-pasangan.',
                'background' => $i('couple/background.jpg'),
                'side_photo' => $i('couple/side-photo.jpg'),
                'bride' => [
                    'nickname' => 'Ayu',
                    'full_name' => '[Nama lengkap mempelai wanita]',
                    'child_label' => 'PUTRI DARI',
                    'father_name' => '[Nama ayah]',
                    'mother_name' => '[Nama ibu]',
                    'photo' => $i('couple/bride.jpg', 'Foto Ayu'),
                ],
                'groom' => [
                    'nickname' => 'Kevin',
                    'full_name' => '[Nama lengkap mempelai pria]',
                    'child_label' => 'PUTRA DARI',
                    'father_name' => '[Nama ayah]',
                    'mother_name' => '[Nama ibu]',
                    'photo' => $i('couple/groom.jpg', 'Foto Kevin'),
                ],
            ],

            'happiest_day' => [
                'title' => 'The Happiest Day',
                'subtitle' => 'Langkah Awal Kami',
                'photo_top' => $i('happiest/photo-top.jpg'),
                'photo_bottom' => $i('happiest/photo-bottom.jpg'),
            ],

            'akad' => self::event([
                'heading' => 'Save the Date',
                'intro' => 'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i, untuk menghadiri acara pernikahan kami:',
                'title' => 'Akad nikah',
                'closing' => 'Mohon do’a dan restu dari tamu sekalian',
                'venue_name' => '[Nama tempat Akad Nikah]',
                'address' => '[Alamat lengkap Akad Nikah]',
                'photos' => [
                    $i('events/akad-1.jpg'), $i('events/akad-2.jpg'),
                    $i('events/akad-3.jpg'), $i('events/akad-4.jpg'),
                ],
            ]),

            'party' => [
                'title' => 'Prepare For The Party',
                'subtitle' => 'Hari bahagia yang akan selalu dikenang',
                'background' => $i('party/background.jpg'),
            ],

            'resepsi' => self::event([
                'title' => 'Resepsi',
                'venue_name' => '[Nama tempat Resepsi]',
                'address' => '[Alamat lengkap Resepsi]',
                'photo_main' => $i('events/resepsi-main.jpg'),
                'photo_side' => $i('events/resepsi-side.jpg'),
            ]),

            'gallery' => [
                'title' => 'Our Moments',
                'description' => 'Momen bahagia yang kami abadikan dalam lensa',
                'group_caption' => 'Yang sudah kami lalui bersama',
                // 9 foto sesuai urutan mockup mobile. Caption lain menyusul dari admin.
                'items' => array_map(
                    fn (int $n) => [
                        'id' => sprintf('default-%02d', $n),
                        'photo' => $i(sprintf('gallery/%02d.jpg', $n)),
                        'caption' => $n === 3 ? 'Our First Meet' : null,
                        'is_active' => true,
                    ],
                    range(1, 9)
                ),
            ],

            'story_intro' => [
                'title' => 'Let See How This Story Begins',
                'subtitle' => 'Hari bahagia yang akan selalu dikenang',
                'photo' => $i('story/intro-oval.png'),
            ],

            'love_story' => [
                'title' => 'Our Love Story',
                'description' => 'Dan di antara tanda-tanda kekuasaan-Nya diciptakan-Nya untukmu pasangan hidup.',
                'items' => [
                    [
                        'id' => 'default-1', 'year' => '2024', 'title' => 'PERTEMUAN',
                        'description' => 'Pertemuan pertama kami, di Danau UNRI',
                        'photos' => [$i('story/2024-1.jpg'), $i('story/2024-2.jpg')],
                    ],
                    [
                        'id' => 'default-2', 'year' => '2025', 'title' => 'TAARUF',
                        'description' => 'Akhirnya kami memutuskan untuk melanjutkan ke jenjang yang lebih serius',
                        'photos' => [$i('story/2025-1.jpg'), $i('story/2025-2.jpg'), $i('story/2025-3.jpg')],
                    ],
                    [
                        'id' => 'default-3', 'year' => '2026', 'title' => 'KOMITMEN',
                        'description' => 'Akhirnya kami memutuskan untuk melanjutkan ke jenjang yang lebih serius',
                        'photos' => [],
                    ],
                ],
            ],

            'rsvp' => [
                'title' => 'Pray For Us',
                'name_label' => 'NAMA',
                'attendance_label' => 'KONFIRMASI KEHADIRAN',
                'attendance_choice_label' => 'PILIH KEHADIRAN',
                'attend_yes_label' => 'Hadir',
                'attend_no_label' => 'Tidak Hadir',
                'count_label' => 'JUMLAH YANG HADIR',
                'message_label' => 'UCAPAN',
                'button_text' => 'KONFIRMASI',
                'wishes_title' => 'UCAPAN',
                'wishes_visible_count' => 2,
                'success_text' => 'Terima kasih, konfirmasi Anda sudah kami terima.',
                'background' => $i('rsvp/background.jpg'),
            ],

            // Kirim Hadiah (setelah Doa & Ucapan): kartu Transfer Bank + kartu QRIS.
            // Bagian ini hanya tampil ke tamu bila ada rekening aktif atau gambar QRIS.
            'gift' => [
                'is_visible' => true,
                'title' => 'Kirim Hadiah',
                'description' => 'Doa restu dan kehadiran Anda merupakan hadiah terindah bagi kami. Namun, apabila Bapak/Ibu/Saudara/i berkenan memberikan tanda kasih, kami telah menyediakan beberapa pilihan berikut.',
                'show_bank' => true,
                'bank_title' => 'Transfer Bank',
                'copy_text' => 'Salin Nomor Rekening',
                // item: id, bank_name, account_number, recipient, note, logo (foto/null), is_active
                'accounts' => [],
                'show_qris' => true,
                'qris_title' => 'QRIS',
                'qris_image' => null,
                'qris_caption' => 'Pindai kode QRIS di atas melalui aplikasi bank atau dompet digital pilihan Anda.',
            ],

            // Dresscode (setelah Resepsi): foto contoh pakaian pria & wanita + warna yang disarankan.
            'dress_code' => [
                'is_visible' => true,
                'title' => 'Dresscode',
                'description' => 'Kami mengundang Bapak/Ibu/Saudara/i untuk mengenakan pakaian dengan warna dan gaya berikut.',
                'men' => ['label' => 'Pria', 'photo' => null, 'text' => null],
                'women' => ['label' => 'Wanita', 'photo' => null, 'text' => null],
                'colors_title' => 'Warna yang Disarankan',
                'colors' => [
                    ['name' => 'Navy', 'hex' => '#1f3557'],
                    ['name' => 'Biru Baja', 'hex' => '#4a6a93'],
                    ['name' => 'Biru Langit', 'hex' => '#8fa9c8'],
                    ['name' => 'Biru Pucat', 'hex' => '#c9d6e6'],
                    ['name' => 'Putih Gading', 'hex' => '#f4f1ea'],
                ],
                'note' => 'Kami sangat menghargai apabila tamu undangan berkenan mengenakan warna-warna di atas agar momen bahagia ini semakin berkesan.',
                'image' => null,
            ],

            'closing' => [
                'title' => 'Mohon Doa Restu',
                'text' => 'Terimakasih Atas do’a dan kehadiran Bapak/Ibu',
                'photo' => $i('closing/photo.png'),
                'background' => $i('closing/background.jpg'),
            ],

            // Lagu latar. Diputar hanya setelah pengunjung menekan tombol play.
            'music' => [
                'enabled' => false,
                'title' => null,
                'artist' => null,
                'file' => null,
            ],
        ];
    }

    private static function event(array $overrides): array
    {
        return array_merge([
            'heading' => null,
            'intro' => null,
            'title' => '',
            'closing' => null,
            'starts_at' => null,          // 'Y-m-d\TH:i', waktu lokal sesuai timezone
            'ends_at' => null,
            'end_label' => 'Selesai',     // ditampilkan jika ends_at kosong
            'timezone' => self::TIMEZONE,
            'venue_name' => '',
            'address' => '',
            'maps_url' => null,
            'latitude' => null,
            'longitude' => null,
            'button_text' => 'LIHAT LOKASI',
            'photos' => [],
            'photo_main' => null,
            'photo_side' => null,
        ], $overrides);
    }
}
