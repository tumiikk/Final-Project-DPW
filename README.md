# Website Undangan Digital Ayu & Kevin — PHP Native

Hasil migrasi project Laravel `wedding-invitationKel5` ke **PHP Native + MySQL** (tanpa framework, tanpa Composer, tanpa Node.js/Vite).
Tampilan, desain, fitur landing page, dan dashboard admin sama dengan versi Laravel. Satu-satunya fitur yang dihapus adalah **QR Code**; link undangan personal tetap ada.

Detail audit, pemetaan Laravel → PHP Native, perubahan database, dan hasil pengujian ada di **[MIGRASI.md](MIGRASI.md)**.

## Pembaruan: Navbar, Dresscode, Kirim Hadiah, ucapan dapat digulir

| Fitur | Di undangan | Pengaturan dashboard |
|---|---|---|
| **Navbar** | Desktop: vertikal di kanan layar. Mobile: horizontal di bawah layar. Navy + emas, muncul setelah tamu menekan *Buka Undangan*. Menu: MEMPELAI, ACARA (submenu Akad, Resepsi, Dresscode), GALERI, HADIAH, DOA & UCAPAN. Menu yang sedang dibaca ditandai otomatis. | Otomatis: menu HADIAH dan pintasan Dresscode hanya muncul bila section-nya tampil. Nama submenu mengikuti judul Akad/Resepsi/Dresscode. |
| **Dresscode** (setelah Resepsi) | Kartu krem berbingkai, foto contoh pakaian PRIA & WANITA, warna yang disarankan, catatan, gambar dresscode utuh (opsional). | *Semua section → Dresscode*: tampil/sembunyi, judul, kalimat pengantar, label + foto + keterangan pria/wanita (unggah, ganti, crop, hapus), daftar warna, catatan, gambar utuh. |
| **Kirim Hadiah** (setelah Doa & Ucapan) | Judul, kalimat pembuka, kartu **Transfer Bank** (kiri/atas) dan kartu **QRIS** (kanan/bawah). Desktop berdampingan, mobile bertumpuk. | *Semua section → Kirim Hadiah*: tampil/sembunyi, judul, kalimat pembuka, tampil/sembunyi tiap kartu, judul kartu, teks tombol salin, tambah/edit/hapus/urutkan rekening (nama bank, nomor, pemilik, keterangan, logo bank opsional), unggah/ganti/hapus gambar QRIS + keterangan. |
| **Doa & Ucapan** | Semua ucapan berada di dalam kotak yang dapat digulir; halaman tidak memanjang walau ucapan bertambah. | *Pray For Us → Jumlah ucapan yang terlihat sebelum digulir* menentukan tinggi kotak. |

Seperti konten lain, perubahan tersimpan sebagai draft dan tampil ke tamu setelah **Publish**. Section Kirim Hadiah hanya tampil bila ada rekening aktif atau gambar QRIS.

**Bila memakai database dari versi sebelumnya:** konten lama tidak ditimpa, sehingga Dresscode dan Kirim Hadiah masih berstatus tersembunyi dengan isi lama. Buka kedua section tersebut di dashboard, klik **Reset section** untuk memuat teks & warna bawaan yang baru (atau isi sendiri), centang "Tampilkan…", simpan, lalu Publish.

---

## 1. Kebutuhan

| Komponen | Versi |
|---|---|
| PHP | 8.2 atau lebih baru (diuji dengan 8.3) |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `fileinfo`, `zip` (untuk export Excel) |
| Database | MySQL 5.7+/8.x atau MariaDB 10.4+ |
| Web server | Apache (dengan `mod_rewrite`) — bawaan Laragon. Server bawaan PHP juga bisa untuk uji cepat. |

Di Laragon semua ekstensi di atas sudah aktif. Bila export Excel menampilkan pesan "Ekstensi PHP zip belum aktif", aktifkan `extension=zip` lewat menu Laragon → PHP → Extensions.

## 2. Struktur folder

```
wedding-invitationKel5/
├── app/
│   ├── bootstrap.php        # memuat .env, helper, autoloader, pengaturan error
│   ├── helpers.php          # fungsi bantu: env(), config(), url(), asset(), view(), json_response(), ...
│   ├── routes.php           # daftar URL (sama dengan routes/web.php Laravel)
│   ├── Core/                # pengganti fitur inti Laravel: Router, Database (PDO), Validator,
│   │                        #   Auth, Csrf, RateLimiter, DatabaseSessionHandler, exception
│   ├── Controllers/         # Admin/*, Auth/LoginController, Invitation/*
│   ├── Models/              # query database per tabel (prepared statement)
│   ├── Services/            # logika konten draft/publish, reset, upload media, link tamu
│   └── Support/             # konten default, aturan validasi section, deskriptor form editor, format tanggal, export
├── cli/
│   ├── install.php          # buat tabel + akun admin + draft default (pengganti migrate & db:seed)
│   └── purge-media.php      # hapus file upload yang tidak terpakai
├── config/                  # app.php (database, session), invitation.php (upload, admin)
├── database/schema.sql      # struktur tabel MySQL (sama dengan migrasi Laravel)
├── docker/entrypoint.sh     # skrip start container di Railway
├── public/                  # DOCUMENT ROOT website
│   ├── index.php            # front controller (semua request masuk ke sini)
│   ├── assets/css, js, fonts# CSS & JavaScript asli (tanpa Vite) + font lokal
│   ├── images/              # foto default & ornamen desain
│   └── storage/invitations/ # file foto & lagu yang diunggah admin
├── storage/logs/            # log error aplikasi
├── views/                   # template HTML (konversi dari Blade)
├── .env.example  .gitignore  .htaccess  Dockerfile  railway.json
```

## 3. Menjalankan di Laragon (lokal)

1. **Salin folder proyek** ke `C:\laragon\www\` (atau folder `www` Laragon Anda), misalnya `C:\laragon\www\wedding-invitationKel5`.
   Bila folder versi Laravel masih ada dengan nama yang sama, ganti dulu namanya (mis. `wedding-invitationKel5-laravel`) sebagai cadangan.
2. **Jalankan Laragon** → klik **Start All** (Apache & MySQL).
3. **Buat file `.env`**: buka *Laragon → Terminal*, lalu:
   ```
   cd C:\laragon\www\wedding-invitationKel5
   copy .env.example .env
   ```
   Buka `.env` dan isi minimal: `ADMIN_PASSWORD` (≥ 10 karakter) serta `APP_URL`. Pengaturan database bawaan Laragon (`root` tanpa password) sudah benar.
4. **Siapkan database** (membuat database `wedding_invitation`, semua tabel, akun admin, dan konten default):
   ```
   php cli/install.php
   ```
   Alternatif tanpa terminal: buat database `wedding_invitation` (utf8mb4_unicode_ci) di phpMyAdmin, lalu impor `database/schema.sql`. Akun admin & konten default tetap perlu dibuat dengan `php cli/install.php`.
5. **Buka website**:
   - Laragon *Auto virtual hosts* aktif (bawaan): `http://wedding-invitationkel5.test` (klik kanan Laragon → Reload Apache bila belum muncul).
   - Atau lewat subfolder: `http://localhost/wedding-invitationKel5/` (juga didukung).
6. **Masuk dashboard**: `/admin/login` → login dengan `ADMIN_USERNAME`/`ADMIN_EMAIL` + `ADMIN_PASSWORD`.
   Periksa konten, klik **Publish**, lalu tambah tamu di **Data tamu & link** dan salin link personalnya.
   Sebelum Publish pertama, halaman publik menampilkan "Undangan ini belum dibuka" (perilaku sama dengan versi Laravel).

Uji cepat tanpa Apache (server bawaan PHP):
```
php -d upload_max_filesize=16M -d post_max_size=20M -S 127.0.0.1:8000 -t public public/index.php
```

### Memakai database lama dari versi Laravel
Struktur tabel tidak berubah, jadi database `wedding_invitation` yang sudah dipakai versi Laravel bisa langsung dipakai: isi `.env` dengan koneksi yang sama lalu jalankan `php cli/install.php` (hanya membuat tabel yang belum ada; data, konten, tamu, RSVP, dan akun admin lama tidak diubah). Password admin lama tetap berlaku.
Bila Anda punya foto/lagu unggahan di `storage/app/public/invitations` versi Laravel, salin isinya ke `public/storage/invitations`.

## 4. Konfigurasi database

Koneksi dibaca dari environment dengan urutan:

1. `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — dipakai di lokal (`.env`).
2. `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` — variabel bawaan Railway MySQL.
3. `MYSQL_URL` / `DATABASE_URL` berbentuk `mysql://user:password@host:port/database`.

Password tidak pernah ditulis di source code. File `.env` sudah masuk `.gitignore`.

Tabel yang dipakai: `users`, `sessions`, `cache`, `invitation_settings`, `invitation_snapshots`, `media_files`, `guests`, `guest_responses` (lihat komentar di `database/schema.sql`).

## 5. Deploy ke Railway

### a. Siapkan repository GitHub
```
git init
git add .
git commit -m "Website undangan PHP Native"
git branch -M main
git remote add origin https://github.com/<akun-anda>/<nama-repo>.git
git push -u origin main
```
Pastikan file `.env` **tidak** ikut ter-push (cek `git status`; `.env` sudah diabaikan oleh `.gitignore`).

### b. Buat project di Railway
1. Railway → **New Project** → **Deploy from GitHub repo** → pilih repository ini. Railway otomatis mendeteksi `Dockerfile` (PHP 8.3 + Apache) dan `railway.json` (healthcheck `/up`).
2. Di project yang sama: **+ Create** → **Database** → **MySQL**.
3. Buka service web → tab **Variables**, tambahkan:

   | Variabel | Nilai |
   |---|---|
   | `MYSQL_URL` | `${{MySQL.MYSQL_URL}}` (referensi ke service MySQL) |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | `https://<domain-anda>.up.railway.app` (isi setelah langkah 4) |
   | `ADMIN_USERNAME` | mis. `admin` |
   | `ADMIN_EMAIL` | email admin |
   | `ADMIN_PASSWORD` | password kuat, minimal 10 karakter |

   Sebagai ganti `MYSQL_URL` Anda juga boleh memakai lima variabel `MYSQLHOST=${{MySQL.MYSQLHOST}}`, `MYSQLPORT=${{MySQL.MYSQLPORT}}`, `MYSQLDATABASE=${{MySQL.MYSQLDATABASE}}`, `MYSQLUSER=${{MySQL.MYSQLUSER}}`, `MYSQLPASSWORD=${{MySQL.MYSQLPASSWORD}}`. Nama service `MySQL` pada referensi harus sama dengan nama service database Anda.
4. Service web → **Settings → Networking → Generate Domain**. Salin domain ke variabel `APP_URL`.
5. **Volume untuk file unggahan (penting)**: service web → **+ Volume** (atau klik kanan service → Attach Volume) dengan **Mount path** `/var/www/html/public/storage`. Tanpa volume, foto/lagu yang diunggah lewat dashboard akan hilang setiap redeploy (sistem file container Railway bersifat sementara).
6. Deploy ulang. Saat container mulai, `docker/entrypoint.sh` otomatis menjalankan `php cli/install.php` (membuat tabel, akun admin, dan draft default; aman diulang) lalu menjalankan Apache pada port dari variabel `PORT` Railway.
7. Buka `https://<domain>/admin/login`, login, cek konten, klik **Publish**.

Catatan Railway:
- Log error PHP tampil di tab **Deployments → Logs** (`LOG_CHANNEL=stderr` sudah diatur di Dockerfile).
- Untuk mengimpor data dari database lokal: ekspor dari phpMyAdmin Laragon (Export → SQL), lalu impor ke Railway memakai koneksi publik MySQL (tab *Connect* di service MySQL) dengan HeidiSQL/MySQL Workbench yang ada di Laragon.
- Mengganti password admin: buat hash dengan `php -r "echo password_hash('PasswordBaru123', PASSWORD_BCRYPT);"` lalu ubah kolom `password` di tabel `users` (Railway: tab *Data* service MySQL; lokal: phpMyAdmin).

## 6. Perintah terminal

| Perintah | Fungsi |
|---|---|
| `php cli/install.php` | Membuat tabel, akun admin dari `.env`, draft konten default (aman diulang) |
| `php cli/purge-media.php` | Menghapus file upload yang tidak dipakai (lebih dari 120 menit) |
| `php cli/purge-media.php --now` | Sama, tanpa masa tenggang |

## 7. Keamanan yang diterapkan
- Password admin di-hash bcrypt (`password_hash` / `password_verify`), kompatibel dengan hash dari Laravel.
- Session disimpan di database, cookie `HttpOnly` + `SameSite=Lax` (+ `Secure` otomatis saat HTTPS), id session diganti setelah login.
- Proteksi CSRF untuk semua POST/PUT/PATCH/DELETE.
- Semua query memakai prepared statement PDO.
- Validasi input sama dengan aturan Laravel asli (per section, RSVP, tamu, login).
- Upload: jenis file dicek dari isi file (fileinfo), ukuran maksimum, dimensi foto, nama file acak; folder upload tidak dapat menjalankan script.
- Batas percobaan: login 5×/menit, RSVP 6×/menit.
- Folder selain `public/` tidak dapat diakses dari browser (`.htaccess` di root mengarahkan ke `public/`).
