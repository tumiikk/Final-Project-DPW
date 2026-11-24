-- ============================================================
-- Struktur database Website Undangan Ayu & Kevin (PHP Native + MySQL)
--
-- Struktur tabel SAMA PERSIS dengan hasil migrasi Laravel asli (nama tabel, kolom, tipe,
-- index, primary key, foreign key), sehingga database lama dapat langsung dipakai.
-- Tabel internal Laravel yang tidak dipakai lagi (migrations, jobs, job_batches,
-- failed_jobs, cache_locks, password_reset_tokens) tidak dibuat; bila sudah ada, boleh dibiarkan.
--
-- Cara pakai: dijalankan otomatis oleh `php cli/install.php`, atau impor manual lewat phpMyAdmin.
-- Aman dijalankan berulang (CREATE TABLE IF NOT EXISTS).
-- ============================================================

-- Akun admin dashboard. Password disimpan sebagai hash bcrypt (password_hash), tidak pernah teks asli.
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session login admin & token CSRF (dipakai DatabaseSessionHandler).
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Penyimpanan sementara; dipakai pembatas percobaan login/RSVP (RateLimiter).
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satu baris (id = 1): undangan online/offline dan waktu publish terakhir.
CREATE TABLE IF NOT EXISTS `invitation_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `is_online` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Konten undangan dalam JSON. Maksimal 2 baris: 'draft' (diedit admin) & 'published' (dilihat tamu).
CREATE TABLE IF NOT EXISTS `invitation_snapshots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL,
  `payload` longtext NOT NULL,
  `content_hash` varchar(40) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_snapshots_status_unique` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan file foto & lagu yang diunggah admin (file default tidak dicatat).
CREATE TABLE IF NOT EXISTS `media_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kind` varchar(10) NOT NULL DEFAULT 'image',
  `disk` varchar(20) NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime` varchar(100) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `width` smallint(5) unsigned DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_files_path_unique` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daftar tamu + token link undangan personal (/undangan/{token}).
CREATE TABLE IF NOT EXISTS `guests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `token` varchar(32) NOT NULL,
  `is_link_active` tinyint(1) NOT NULL DEFAULT 1,
  `link_regenerated_at` timestamp NULL DEFAULT NULL,
  `max_attendees` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guests_token_unique` (`token`),
  KEY `guests_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RSVP & ucapan. Satu tamu maksimal satu baris (guest_id UNIQUE), ikut terhapus bila tamu dihapus.
CREATE TABLE IF NOT EXISTS `guest_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guest_id` bigint(20) unsigned NOT NULL,
  `attendance` varchar(20) NOT NULL,
  `attendee_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `submitted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_responses_guest_id_unique` (`guest_id`),
  KEY `guest_responses_attendance_index` (`attendance`),
  KEY `guest_responses_is_hidden_index` (`is_hidden`),
  CONSTRAINT `guest_responses_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
