<?php

/**
 * Konfigurasi khusus undangan (dipindahkan dari config/invitation.php Laravel).
 */
return [

    /*
    | Akun admin awal. Nilai dibaca dari .env / Environment Variables, JANGAN diisi password di file ini.
    | Dipakai oleh cli/install.php (hanya membuat akun jika belum ada).
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'username' => env('ADMIN_USERNAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'uploads' => [
        // Folder fisik tempat file unggahan disimpan; URL publiknya /storage/<path>.
        // Di Railway, pasang Volume pada folder ini (/var/www/html/public/storage) agar file tidak hilang saat redeploy.
        'root' => BASE_PATH.'/public/storage',
        // Nama "disk" yang dicatat di tabel media_files (dipertahankan dari Laravel).
        'disk' => 'public',
        'dir' => 'invitations',
        // Subfolder yang boleh dipakai saat upload foto
        'groups' => ['cover', 'couple', 'events', 'gallery', 'story', 'gift', 'dress', 'misc'],
        'image' => [
            'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_kb' => 5120,        // 5 MB
            'min_width' => 200,
            'min_height' => 200,
            'max_dimension' => 8000, // piksel, sisi terpanjang
            // Batas minimal khusus per grup: logo bank boleh berukuran kecil/lebar
            'min_by_group' => ['gift' => [60, 30]],
        ],
        'audio' => [
            'mimes' => ['mp3', 'm4a', 'ogg', 'wav'],
            'max_kb' => 15360,       // 15 MB
        ],
        // File upload yang tidak dipakai draft/published baru dihapus setelah selang ini
        'orphan_grace_minutes' => 120,
    ],

];
