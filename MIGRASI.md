# Laporan Migrasi Laravel → PHP Native

Dokumen ini merangkum Tahap 1 (audit), Tahap 2 (pemetaan), Tahap 3 (migrasi), dan Tahap 4 (pengujian).

## 1. Audit project asli (Laravel 12)

**Teknologi asli:** Laravel 12.7x (PHP ≥ 8.2), Blade, Vite 7 (+ `laravel-vite-plugin`), font lokal `@fontsource/*`, MySQL, `endroid/qr-code` (QR SVG). Tanpa React/Tailwind.

### Landing page (urutan section)
| Section | Isi & interaksi |
|---|---|
| Cover | Nama mempelai, tanggal otomatis dari Akad/Resepsi, **nama tamu personal**, tombol *BUKA UNDANGAN* (halaman terkunci sampai diklik) |
| Introduction | "A little note of love", kalimat, 2 foto |
| Kutipan | QS. Ar-Rum: 21 di kartu, foto latar |
| Pengantar mempelai | "Yang Berbahagia" |
| Bride & Groom | Banner, foto & nama panggilan mempelai, nama orang tua |
| The Happiest Day | Judul, subjudul, 2 foto |
| Akad Nikah | Save the Date: tanggal, jam (zona waktu), tempat, alamat, peta OpenStreetMap (bila koordinat diisi), tombol Google Maps, kolase 4 foto |
| Prepare For The Party | Foto latar + **hitung mundur** (tampil bila tanggal diisi) |
| Resepsi | Tanggal, jam, tempat, alamat, peta, tombol lokasi, foto lengkung + foto pendamping |
| Our Moments (galeri) | Kolase 9 foto sesuai mockup + baris tambahan, caption, **lightbox** (klik, panah, ESC) |
| Love Story | Intro oval + timeline per tahun, foto per cerita (0/1/2–3/4+ foto) |
| Pray For Us (RSVP & ucapan) | Form RSVP hanya di link personal (hadir/tidak, jumlah hadir dibatasi per tamu, ucapan), kirim tanpa reload, kotak ucapan + "Lihat ucapan lainnya" |
| Informasi Hadiah | Opsional (`is_visible`), rekening + tombol salin |
| Dress Code | Opsional, warna, catatan, gambar referensi |
| Penutup | "Mohon Doa Restu", foto oval, foto latar |
| Musik | Tanpa autoplay; mulai setelah *Buka Undangan*, tombol play/pause |
| Lainnya | Animasi muncul saat scroll (menghormati *reduced motion*), halaman "undangan tidak tersedia" (link salah/nonaktif = 404, belum publish/offline = 503) |

### Dashboard admin
Login (email **atau** username, desain mockup), Ringkasan (statistik dari database, status draft/published, saklar online/offline, daftar info yang belum diisi), Semua section (17 editor), Editor per section (form dinamis, hitung karakter, upload foto, pilih foto default, **crop/posisi non-destruktif**, teks alternatif, daftar item yang bisa diurutkan/hapus, upload lagu, Simpan draft, Batalkan perubahan, Reset section, peringatan perubahan belum disimpan, deteksi konflik antar-tab), **Live Preview** desktop (1440 px diskalakan) & mobile (390 px), Preview seluruh undangan, **Publish**, Data tamu & link (cari, filter RSVP/link, tambah/edit/hapus, salin link, nonaktif/aktif, buat ulang link, ~~QR~~), RSVP & ucapan (filter, sembunyikan/tampilkan, hapus), Export `.xlsx`/`.csv` (tamu, RSVP, jumlah kehadiran, ucapan), endpoint Reset semua (`POST /admin/reset-all`, wajib `confirm`).

### Database (MySQL)
`users` (+`username`), `guests` (token 24 karakter UNIQUE, link aktif/nonaktif, `max_attendees`), `guest_responses` (FK `guest_id` → `guests.id` ON DELETE CASCADE, UNIQUE: satu tamu satu respons), `invitation_snapshots` (baris `draft` & `published`, payload JSON, hash, versi), `invitation_settings` (1 baris), `media_files` (catatan upload), plus tabel framework `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `migrations`. Konten default ada di kode (`DefaultContent`), bukan di database.

## 2. Pemetaan Laravel → PHP Native

| Laravel | PHP Native |
|---|---|
| `public/index.php` + Kernel + middleware | `public/index.php` (front controller, penanganan error) |
| `routes/web.php` | `app/routes.php` + `app/Core/Router.php` (path & metode identik) |
| Controllers (`app/Http/Controllers`) | `app/Controllers` (nama & method sama, return → fungsi respons) |
| FormRequest (`SectionRequest`, `RsvpRequest`, `GuestRequest`, `LoginRequest`, `Upload*Request`) | `app/Core/Validator.php` (aturan Laravel yang sama, termasuk wildcard & `validated()`), pemeriksaan upload di `MediaController` |
| Rule `GoogleMapsUrl` | fungsi validasi di `SectionController` (memakai `MapsLink`) |
| Eloquent Models | `app/Models/*` dengan PDO prepared statement |
| Services | `app/Services/*` (logika sama) |
| Support (`DefaultContent`, `SectionSchema`, `AdminFields`, `MapsLink`, `XlsxWriter`, dll.) | disalin hampir tanpa perubahan; `EventFormat` tanpa Carbon (nama hari/bulan Indonesia ditulis langsung) |
| Auth facade, middleware `auth`/`guest` | `app/Core/Auth.php` (session + `password_verify`) |
| Session driver database | `app/Core/DatabaseSessionHandler.php` (tabel `sessions` yang sama) |
| VerifyCsrfToken | `app/Core/Csrf.php` (`_token` / header `X-CSRF-TOKEN`) |
| RateLimiter `login`, `rsvp` | `app/Core/RateLimiter.php` (tabel `cache`) |
| Middleware TrimStrings & ConvertEmptyStringsToNull | `request_input()` di `app/helpers.php` |
| Storage disk `public` + `storage:link` | file langsung di `public/storage/invitations` (URL `/storage/...` tetap sama) |
| Blade views & komponen `<x-inv.photo>` | `views/*.php` + helper `inv_photo()`; HTML hasil render identik |
| Vite + `@vite` | CSS/JS sumber asli disajikan langsung (`<script type="module">`), font `@fontsource` → `public/assets/css/fonts.css` + `public/assets/fonts` |
| `config/*.php` + `.env` | `config/app.php`, `config/invitation.php`, loader `.env` di `helpers.php` |
| Migrations + seeders | `database/schema.sql` + `cli/install.php` |
| Artisan `invitation:purge-media` | `cli/purge-media.php` |
| `endroid/qr-code` + fitur QR | **dihapus** |

**Dipertahankan tanpa perubahan visual:** seluruh CSS (`invitation.css`, `admin.css`), seluruh JavaScript landing & dashboard, semua gambar/ornamen/foto default, struktur HTML semua halaman.

**Perubahan teknis pada aset (tanpa efek visual):** `@import '@fontsource/...'` diganti `@import url('fonts.css')` (file font identik); URL gambar di CSS menjadi relatif (`../../images/...`); `common.js` mendapat `appUrl()` agar dashboard juga berjalan dari subfolder; `guests.js` tanpa tombol/dialog QR.

**Dihapus karena khusus QR Code:** route `guests/{id}/qr` & `guests/{id}/qr/download`, `GuestLinkService::qrSvg()`, method `qr()`/`qrDownload()`, tombol "QR", dialog QR, aturan CSS `.qr`, dependensi `endroid/qr-code`. Teks "salin link atau unduh QR" → "salin link", "Link & QR Code baru dibuat" → "Link baru dibuat". QR tidak pernah disimpan di database, jadi tidak ada tabel/kolom yang dihapus.

**Tidak dibawa (khusus framework):** `vendor/`, `node_modules/`, `bootstrap/`, `artisan`, `composer.*`, `package*.json`, `vite.config.js`, `phpunit.xml`, `tests/` (PHPUnit Laravel), `resources/` (dipindah ke `public/assets` & `views`), `storage/framework`, `storage/app/public/images` (duplikat `public/images`), `database.sqlite` (kosong).

## 3. Database

- Struktur 8 tabel aplikasi **identik** dengan hasil migrasi Laravel di MySQL (didapat dari `mysqldump` database hasil `php artisan migrate`): nama kolom, tipe, default, index, UNIQUE, primary key, dan foreign key `guest_responses.guest_id → guests.id ON DELETE CASCADE`.
- Tabel internal Laravel yang tidak lagi dipakai (`migrations`, `jobs`, `job_batches`, `failed_jobs`, `cache_locks`, `password_reset_tokens`) tidak dibuat di instalasi baru. Pada database lama tabel tersebut boleh dibiarkan; aplikasi tidak menyentuhnya.
- Data lama tetap terbaca: payload JSON konten, token tamu, RSVP, hash password admin (bcrypt), dan path upload (`invitations/...`).
- Waktu disimpan dengan cara yang sama (zona aplikasi UTC, koneksi MySQL tanpa mengubah `time_zone`), sehingga data lama tidak bergeser.
- Session lama buatan Laravel diabaikan (admin cukup login ulang sekali).

## 4. Pengujian yang dilakukan

Versi Laravel asli dijalankan berdampingan dengan versi PHP Native pada MariaDB 10.11 (PHP 8.3), lalu keduanya diberi request yang sama dan hasilnya dibandingkan otomatis.

- **HTML identik** (setelah menormalkan domain, nama file aset hasil Vite, token CSRF/tamu, dan ID acak): landing, undangan personal (sebelum/sesudah RSVP, ucapan disembunyikan), halaman "tidak tersedia" (token salah, link nonaktif, token lama setelah buat ulang, offline), Ringkasan, daftar section, 17 halaman editor, RSVP & ucapan, preview seluruh undangan, dan render Live Preview ke-17 section. Perbedaan yang tersisa hanya yang disengaja: tombol/dialog QR dan komentar kode.
- **JSON API identik**: status publish, dashboard, data section, simpan section (termasuk trimming, field asing dibuang, item baru diberi ID), 10 skenario validasi (link Maps bukan Google, waktu selesai < mulai, lat/long tidak berpasangan, wajib/maks/boolean, item bersarang, rekening, hex warna, upload tidak dikenal, reset-all tanpa konfirmasi, konflik versi 409), daftar & filter tamu, RSVP (valid, tidak valid, melebihi jatah, link nonaktif 403), moderasi, hapus, reset, batalkan perubahan, export CSV (isi sama), 401/419/404/405.
- **Perilaku lain sama**: login gagal/kosong, login dengan email huruf besar, kembali ke halaman tujuan setelah login, logout, batas 5× login per menit (percobaan ke-6 = 429), RSVP tanpa JavaScript (redirect `#rsvp`, pesan sukses sekali tampil, error + isian lama), upload foto (JPG/PNG/WebP, tolak PDF/GIF/terlalu kecil/terlalu besar/grup salah), upload lagu MP3, lagu tidak boleh berupa foto, crop tersimpan di landing.
- **Screenshot browser (Chromium)** landing desktop 1440 px & mobile 390 px (cover, seluruh undangan setelah dibuka, lightbox), login, ringkasan, section, editor + Live Preview (desktop/mobile/live edit), preview penuh, RSVP & ucapan, dashboard mobile: **identik** dengan versi Laravel saat dibandingkan per piksel (0 piksel berbeda di luar anti-aliasing teks; satu-satunya perbedaan nyata adalah tombol QR yang dihapus di halaman tamu). Tidak ada error JavaScript di console.
- **Apache + mod_php (seperti Laragon)**: document root `public/`, document root folder proyek, dan subfolder `http://localhost/<folder>/` — alur lengkap login → edit → Live Preview → simpan → publish → upload foto → tambah tamu → export → tamu membuka link → RSVP → moderasi berjalan; file `.env`, `app/`, `config/`, `cli/` tidak dapat dibuka dari browser.
- **Konfigurasi Railway**: koneksi lewat variabel `MYSQLHOST…` dan `MYSQL_URL` diuji dengan `cli/install.php`. Image Docker tidak dapat di-build di lingkungan pengujian (akses Docker Hub tertutup), jadi `Dockerfile` belum diuji langsung di Railway.

## 5. Catatan perilaku kecil

- Admin yang sudah login lalu membuka `/admin/login` diarahkan ke Ringkasan (versi Laravel mengarahkan ke `/`, yang menampilkan undangan publik).
- Pesan validasi bawaan tetap berbahasa Inggris seperti versi Laravel (pesan kustom berbahasa Indonesia dipertahankan).
- Upload yang melebihi batas server (`post_max_size`) kini dijawab 413 dengan pesan berbahasa Indonesia. Batas upload PHP dinaikkan ke 16/20 MB lewat `public/.htaccess`, `public/.user.ini`, dan Dockerfile agar sesuai batas aplikasi (foto 5 MB, lagu 15 MB).
