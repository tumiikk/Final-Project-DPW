<?php

/**
 * Fungsi-fungsi bantu global (pengganti helper bawaan Laravel seperti env(), config(),
 * url(), asset(), old(), csrf_token(), view(), response()->json(), redirect(), abort()).
 * File ini dimuat sekali oleh app/bootstrap.php.
 */

use App\Core\HttpException;

// ============================================================
// ENVIRONMENT & KONFIGURASI
// ============================================================

/**
 * Membaca file .env (format KEY=VALUE) ke dalam penyimpanan internal.
 * Variabel yang sudah ada di environment server (mis. Railway) TIDAK ditimpa,
 * sehingga konfigurasi production selalu menang atas file .env.
 */
function load_env(string $file): void
{
    if (! is_file($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (str_starts_with($key, 'export ')) {
            $key = trim(substr($key, 7));
        }

        // Nilai berkutip: ambil isi di dalam kutip. Nilai tanpa kutip: buang komentar di belakang.
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            $end = strpos($value, $quote, 1);
            $value = $end === false ? substr($value, 1) : substr($value, 1, $end - 1);
        } else {
            $value = trim(preg_replace('/\s+#.*$/', '', $value));
        }

        // Mendukung ${NAMA_VARIABEL} di dalam nilai
        $value = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/i', fn ($m) => (string) env($m[1], ''), $value);

        if (env_raw($key) === null) {
            $GLOBALS['__env'][$key] = $value;
        }
    }
}

/** Nilai mentah variabel environment (server lebih dulu, lalu isi file .env). */
function env_raw(string $key): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value !== false && $value !== null && ! is_array($value)) {
        return (string) $value;
    }

    return $GLOBALS['__env'][$key] ?? null;
}

/** Mengambil variabel environment. Teks "null" dianggap null. */
function env(string $key, mixed $default = null): mixed
{
    $value = env_raw($key);
    if ($value === null || strtolower($value) === 'null') {
        return $default;
    }

    return $value;
}

/** Mengambil nilai konfigurasi dengan notasi titik, contoh config('invitation.uploads.dir'). */
function config(string $key, mixed $default = null): mixed
{
    static $cache = [];
    $file = strtok($key, '.');

    if (! array_key_exists($file, $cache)) {
        $path = BASE_PATH.'/config/'.$file.'.php';
        $cache[$file] = is_file($path) ? require $path : [];
    }

    $rest = substr($key, strlen($file) + 1);

    return $rest === '' || $rest === false ? $cache[$file] : data_get($cache[$file], $rest, $default);
}

// ============================================================
// ARRAY & TEKS
// ============================================================

/** Mengambil nilai array bertingkat memakai notasi titik ("couple.bride.full_name"). */
function data_get(mixed $target, string $path, mixed $default = null): mixed
{
    foreach (explode('.', $path) as $segment) {
        if (is_array($target) && array_key_exists($segment, $target)) {
            $target = $target[$segment];
        } else {
            return $default;
        }
    }

    return $target;
}

/** Mengisi nilai array bertingkat memakai notasi titik (membuat level baru bila belum ada). */
function data_set(array &$target, string $path, mixed $value): void
{
    $keys = explode('.', $path);
    $last = array_pop($keys);
    $ref = &$target;
    foreach ($keys as $segment) {
        if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
            $ref[$segment] = [];
        }
        $ref = &$ref[$segment];
    }
    $ref[$last] = $value;
}

/** True bila nilai berisi (bukan null, bukan string kosong/spasi, bukan array kosong). Sama dengan filled() Laravel. */
function filled(mixed $value): bool
{
    if ($value === null) {
        return false;
    }
    if (is_string($value)) {
        return trim($value) !== '';
    }
    if (is_array($value)) {
        return $value !== [];
    }

    return true;
}

/** ID unik berurutan waktu (format ULID, 26 karakter Crockford Base32) untuk nama file & id item. */
function ulid(): string
{
    $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    $time = (int) floor(microtime(true) * 1000);
    $out = '';
    for ($i = 9; $i >= 0; $i--) {
        $out = $alphabet[$time % 32].$out;
        $time = intdiv($time, 32);
    }
    for ($i = 0; $i < 16; $i++) {
        $out .= $alphabet[random_int(0, 31)];
    }

    return $out;
}

/** Teks acak huruf kecil + angka, dipakai untuk token link undangan tamu. */
function random_token(int $length): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $out;
}

// ============================================================
// WAKTU
// ============================================================

/** Waktu sekarang dalam format kolom TIMESTAMP MySQL (zona waktu aplikasi, default UTC). */
function now_db(): string
{
    return date('Y-m-d H:i:s');
}

/** Mengubah nilai TIMESTAMP database menjadi ISO-8601 (contoh 2026-11-22T01:16:00+00:00) atau null. */
function to_iso(?string $db): ?string
{
    if ($db === null || $db === '') {
        return null;
    }

    return (new DateTimeImmutable($db))->format('c');
}

// ============================================================
// REQUEST
// ============================================================

/** Metode HTTP. Form HTML dapat memakai input tersembunyi _method (PUT/PATCH/DELETE). */
function request_method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper((string) $_POST['_method']);
    }

    return $method;
}

/**
 * Prefix URL tempat aplikasi berjalan. Kosong bila di root domain (Laragon virtual host, Railway).
 * Berisi "/nama-folder" bila dibuka lewat http://localhost/nama-folder.
 */
function base_path_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    // Kasus 1: URL memuat folder public (mis. /proyek/public/admin)
    // Kasus 2: root proyek dibuka langsung dan .htaccess di root meneruskan ke public/ (mis. /proyek/admin)
    if ($dir !== '' && str_starts_with($uri.'/', $dir.'/')) {
        $base = $dir;
    } elseif (str_ends_with($dir, '/public') && str_starts_with($uri.'/', substr($dir, 0, -7).'/')) {
        $base = substr($dir, 0, -7);
    } else {
        $base = '';
    }

    return $base;
}

/** Path request tanpa prefix folder, contoh "/admin/dashboard". */
function request_path(): string
{
    $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $path = substr($uri, strlen(base_path_url())) ?: '/';
    $path = '/'.trim($path, '/');

    return $path === '/index.php' ? '/' : $path;
}

/** True bila koneksi HTTPS (termasuk di belakang proxy seperti Railway). */
function is_https(): bool
{
    return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/** Alamat IP pengunjung (dipakai pembatas percobaan login/RSVP). */
function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Seluruh input request (query string + body form/JSON) yang sudah dibersihkan
 * seperti middleware Laravel TrimStrings & ConvertEmptyStringsToNull:
 * teks di-trim dan teks kosong diubah menjadi null (kecuali field password).
 */
function request_input(): array
{
    static $input = null;
    if ($input !== null) {
        return $input;
    }

    $body = $_POST;
    $type = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
    if (str_contains($type, 'json')) {
        $json = json_decode((string) file_get_contents('php://input'), true);
        $body = is_array($json) ? $json : [];
    }

    $clean = function (mixed $value, string $key) use (&$clean): mixed {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $clean($v, (string) $k);
            }

            return $value;
        }
        if (is_string($value)) {
            if (! in_array($key, ['password', 'password_confirmation', 'current_password'], true)) {
                $value = trim($value);
            }

            return $value === '' ? null : $value;
        }

        return $value;
    };

    $input = [];
    foreach (array_replace($_GET, $body) as $k => $v) {
        $input[$k] = $clean($v, (string) $k);
    }

    return $input;
}

/** Mengambil satu input (mendukung notasi titik). */
function input(string $key, mixed $default = null): mixed
{
    return data_get(request_input(), $key, $default);
}

/** Header request, contoh request_header('X-CSRF-TOKEN'). */
function request_header(string $name): ?string
{
    $key = 'HTTP_'.strtoupper(str_replace('-', '_', $name));

    return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : null;
}

/** True bila klien (JavaScript fetch) meminta respons JSON. Pengganti $request->expectsJson(). */
function expects_json(): bool
{
    return str_contains(strtolower(request_header('Accept') ?? ''), 'json')
        || strtolower(request_header('X-Requested-With') ?? '') === 'xmlhttprequest';
}

// ============================================================
// URL & ASET
// ============================================================

/** URL relatif terhadap root website, contoh url('/admin/login') => "/admin/login". */
function url(string $path = '/'): string
{
    return base_path_url().'/'.ltrim($path, '/');
}

/** URL file statis di folder public/ (gambar, CSS, JS). */
function asset(string $path): string
{
    return url($path);
}

/** URL CSS/JS dengan penanda versi (waktu ubah file) agar browser memuat versi terbaru setelah update. */
function asset_v(string $path): string
{
    $file = BASE_PATH.'/public/'.ltrim($path, '/');

    return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
}

/**
 * URL lengkap (dengan domain) untuk link yang dibagikan ke luar, contoh link undangan tamu.
 * Memakai APP_URL bila diisi; jika kosong, memakai domain request saat ini.
 */
function absolute_url(string $path = '/'): string
{
    $root = (string) config('app.url');
    if ($root === '') {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $root = (is_https() ? 'https' : 'http').'://'.$host.base_path_url();
    }

    return rtrim($root, '/').'/'.ltrim($path, '/');
}

/** Lokasi fisik file di folder public/. */
function public_path(string $path = ''): string
{
    return BASE_PATH.'/public'.($path !== '' ? '/'.ltrim($path, '/') : '');
}

// ============================================================
// SESSION, FLASH, OLD INPUT, CSRF
// ============================================================

/** Menyimpan data sekali pakai untuk request berikutnya (pesan sukses, error validasi, old input). */
function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

/** Membaca data flash dari request sebelumnya. */
function session_flash(string $key, mixed $default = null): mixed
{
    return $GLOBALS['__flash'][$key] ?? $default;
}

/** Input lama setelah validasi gagal (mengisi ulang form). Pengganti old() Laravel. */
function old(string $key, mixed $default = null): mixed
{
    $old = session_flash('_old_input', []);

    return array_key_exists($key, $old) ? $old[$key] : $default;
}

/** Semua pesan error validasi dari request sebelumnya: ['field' => ['pesan', ...]]. */
function validation_errors(): array
{
    return session_flash('errors', []);
}

/** Token CSRF milik session ini (dibuat bila belum ada). */
function csrf_token(): string
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(20));
    }

    return $_SESSION['_token'];
}

/** Input tersembunyi berisi token CSRF untuk form HTML. Pengganti @csrf. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="'.e(csrf_token()).'" autocomplete="off">';
}

// ============================================================
// VIEW & RESPONS
// ============================================================

/** Escape HTML (pengganti {{ }} Blade). */
function e(mixed $value): string
{
    if (is_bool($value)) {
        $value = $value ? '1' : '';
    }

    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8', true);
}

/** Merender file views/<nama>.php dengan variabel yang diberikan, hasilnya berupa string HTML. */
function view(string $name, array $vars = []): string
{
    $__file = BASE_PATH.'/views/'.str_replace('.', '/', $name).'.php';
    if (! is_file($__file)) {
        throw new RuntimeException("View tidak ditemukan: $name");
    }

    extract($vars, EXTR_SKIP);
    ob_start();
    try {
        require $__file;
    } catch (Throwable $ex) {
        ob_end_clean();
        throw $ex;
    }

    return (string) ob_get_clean();
}

/**
 * Komponen foto undangan (pengganti komponen Blade <x-inv.photo>).
 * Menghasilkan <span class="ph ..."><img ...></span>. Posisi crop disimpan sebagai variabel CSS
 * (--fx, --fy, --fz) sehingga file foto asli tidak pernah diubah.
 */
function inv_photo(mixed $ref, array $attrs = [], ?string $alt = null, bool $eager = false): string
{
    $ref = is_array($ref) ? $ref : null;
    $url = (new App\Services\MediaService())->url($ref);
    $alt ??= $ref['alt'] ?? '';
    $style = trim(($attrs['style'] ?? '').';'.App\Support\Inv::cropStyle($ref), ';');
    $class = trim('ph '.($attrs['class'] ?? ''));

    return '<span class="'.e($class).'"'.($style !== '' ? ' style="'.e($style).'"' : '').'>'
        .'<img src="'.e($url).'" alt="'.e($alt).'" loading="'.($eager ? 'eager' : 'lazy').'" decoding="async"></span>';
}

/** Mengirim respons HTML. */
function html_response(string $html, int $status = 200, array $headers = []): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    foreach ($headers as $k => $v) {
        header("$k: $v");
    }
    echo $html;
}

/** Mengirim respons JSON (dipakai endpoint yang dipanggil JavaScript). */
function json_response(mixed $data, int $status = 200, array $headers = []): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    foreach ($headers as $k => $v) {
        header("$k: $v");
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** Struktur JSON halaman data (sama dengan paginator Laravel yang dibaca JavaScript dashboard). */
function paginator(array $items, int $total, int $page, int $perPage): array
{
    $page = max(1, $page);
    $from = ($page - 1) * $perPage + 1;

    return [
        'current_page' => $page,
        'data' => array_values($items),
        'from' => $items ? $from : null,
        'last_page' => max(1, (int) ceil($total / $perPage)),
        'per_page' => $perPage,
        'to' => $items ? $from + count($items) - 1 : null,
        'total' => $total,
    ];
}

/** Respons kosong 204 (berhasil tanpa isi). */
function no_content(): void
{
    http_response_code(204);
}

/** Mengalihkan browser ke URL lain. */
function redirect(string $to, int $status = 302): void
{
    header('Location: '.$to, true, $status);
    http_response_code($status);
}

/** URL halaman sebelumnya (untuk redirect kembali setelah validasi gagal). */
function previous_url(string $fallback = '/'): string
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    // Hanya terima referer dari domain yang sama (mencegah open redirect)
    if ($ref !== '' && $host !== '' && parse_url($ref, PHP_URL_HOST).(parse_url($ref, PHP_URL_PORT) ? ':'.parse_url($ref, PHP_URL_PORT) : '') === $host) {
        return $ref;
    }

    return url($fallback);
}

/** Menghentikan request dengan status HTTP tertentu (404, 403, dst). */
function abort(int $status, string $message = ''): never
{
    throw new HttpException($status, $message);
}

// ============================================================
// LOG
// ============================================================

/** Mencatat error ke storage/logs/app.log (atau ke log server bila folder tidak dapat ditulis). */
function log_error(string $message, array $context = []): void
{
    $line = '['.date('Y-m-d H:i:s').'] '.$message.($context ? ' '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '').PHP_EOL;
    $file = BASE_PATH.'/storage/logs/app.log';

    // LOG_CHANNEL=stderr (dipakai di Docker/Railway): log tampil di menu Logs Railway
    if (env('LOG_CHANNEL') === 'stderr' || ! @file_put_contents($file, $line, FILE_APPEND | LOCK_EX)) {
        error_log(trim($line));
    }
}
