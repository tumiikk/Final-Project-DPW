<?php

/**
 * FRONT CONTROLLER
 * Semua request (kecuali file statis seperti gambar/CSS/JS) diarahkan ke file ini oleh .htaccess.
 * Alurnya: bootstrap -> session -> CSRF -> router -> controller -> respons.
 * Error ditangkap di sini lalu diubah menjadi JSON (untuk JavaScript) atau halaman error (untuk browser).
 */

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DatabaseSessionHandler;
use App\Core\HttpException;
use App\Core\RateLimiter;
use App\Core\Router;
use App\Core\ValidationException;

$projectRoot = dirname(__DIR__);

if (!is_file($projectRoot . '/app/bootstrap.php')) {
    $projectRoot = '/home/usmfnlhj/repositories/Final-Project-DPW';
}

require $projectRoot . '/app/bootstrap.php';

// Server bawaan PHP (php -S): file statis yang ada dilayani langsung tanpa melewati aplikasi.
if (PHP_SAPI === 'cli-server') {
    $static = __DIR__.parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($static !== __DIR__.'/' && is_file($static)) {
        return false;
    }
}

try {
    // Endpoint cek kesehatan (dipakai healthcheck Railway), tanpa session & database.
    if (request_path() === '/up') {
        html_response('<!doctype html><title>OK</title><p>Application up</p>');

        return;
    }

    // ---------- Session disimpan di tabel `sessions` ----------
    $cfg = config('app.session');
    session_set_save_handler(new DatabaseSessionHandler($cfg['lifetime']), true);
    session_name($cfg['cookie']);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_probability', '2');
    ini_set('session.gc_divisor', '100');
    ini_set('session.gc_maxlifetime', (string) ($cfg['lifetime'] * 60));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path_url() ?: '/',
        'secure' => $cfg['secure'] !== null ? filter_var($cfg['secure'], FILTER_VALIDATE_BOOLEAN) : is_https(),
        'httponly' => true,   // cookie tidak bisa dibaca JavaScript
        'samesite' => 'Lax',
    ]);
    session_start();

    // Data flash dari request sebelumnya dipindah ke memori, lalu dihapus dari session (sekali pakai)
    $GLOBALS['__flash'] = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    csrf_token();

    // ---------- Proteksi CSRF untuk POST/PUT/PATCH/DELETE ----------
    Csrf::verify();

    // ---------- Routing ----------
    $router = new Router();
    $router->middleware('auth', fn () => Auth::requireLogin());
    $router->middleware('guest', fn () => Auth::requireGuest());
    $router->middleware('throttle', fn (?string $name, array $params) => RateLimiter::throttle((string) $name, $params));
    require BASE_PATH.'/app/routes.php';

    $router->dispatch(request_method(), request_path());
} catch (ValidationException $e) {
    // Input tidak valid
    if (expects_json()) {
        json_response(['message' => $e->getMessage(), 'errors' => $e->errors], 422);
    } else {
        // Form HTML: kembali ke halaman sebelumnya membawa pesan error & isian lama (tanpa password)
        $old = request_input();
        unset($old['password'], $old['_token']);
        flash('errors', $e->errors);
        flash('_old_input', $old);
        redirect(previous_url());
    }
} catch (HttpException $e) {
    if ($e->status >= 300 && $e->status < 400) {
        redirect($e->headers['Location'] ?? url('/'), $e->status);
    } elseif (expects_json()) {
        $default = [401 => 'Unauthenticated.', 403 => 'This action is unauthorized.', 404 => 'Not Found', 405 => 'Method Not Allowed', 419 => 'CSRF token mismatch.', 429 => 'Too Many Attempts.'];
        json_response(['message' => $e->getMessage() ?: ($default[$e->status] ?? 'Error')], $e->status, $e->headers);
    } else {
        html_response(view('errors.minimal', ['code' => $e->status]), $e->status, $e->headers);
    }
} catch (Throwable $e) {
    // Error tak terduga: catat di log, tampilkan detail hanya bila APP_DEBUG=true
    log_error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    if (! headers_sent()) {
        if (expects_json()) {
            json_response(['message' => config('app.debug') ? $e->getMessage() : 'Server Error'], 500);
        } else {
            html_response(view('errors.minimal', ['code' => 500, 'debug' => config('app.debug') ? $e : null]), 500);
        }
    }
}
