<?php

/**
 * DAFTAR ROUTE (pengganti routes/web.php Laravel). Path & metode HTTP sama persis dengan project asli,
 * sehingga JavaScript dashboard tidak perlu diubah.
 *
 * Format: $router->add('METODE', '/path/{param}', [Controller::class, 'method'], [middleware], [regex param]);
 * Middleware: 'auth' = wajib login admin, 'guest' = hanya untuk yang belum login,
 *             'throttle:login' / 'throttle:rsvp' = batas jumlah percobaan.
 *
 * Fitur QR Code (route guests/{id}/qr dan guests/{id}/qr/download) DIHAPUS sesuai permintaan.
 * Link undangan personal (/undangan/{token}) tetap dipertahankan.
 */

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ExportController;
use App\Controllers\Admin\GuestController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\PreviewController;
use App\Controllers\Admin\PublishController;
use App\Controllers\Admin\ResetController;
use App\Controllers\Admin\ResponseController;
use App\Controllers\Admin\SectionController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Invitation\GuestResponseController;
use App\Controllers\Invitation\InvitationController;
use App\Models\Guest;
use App\Support\DefaultContent;

/** @var \App\Core\Router $router */

$token = '[a-z0-9]{'.Guest::TOKEN_LENGTH.'}';
$section = implode('|', DefaultContent::sections()); // hanya nama section yang dikenal
$id = '[0-9]+';

// ---- Publik ----
$router->add('GET', '/', [InvitationController::class, 'landing']);
// Token berformat apa pun diterima di sini: yang tidak dikenal tampil sebagai halaman "tidak tersedia" bertema.
$router->add('GET', '/undangan/{token}', [InvitationController::class, 'show'], [], ['token' => '[A-Za-z0-9_\-]{1,100}']);
$router->add('POST', '/undangan/{token}/rsvp', [GuestResponseController::class, 'store'], ['throttle:rsvp'], ['token' => $token]);

// ---- Auth admin ----
$router->add('GET', '/admin/login', [LoginController::class, 'show'], ['guest']);
$router->add('POST', '/admin/login', [LoginController::class, 'login'], ['guest', 'throttle:login']);
$router->add('POST', '/admin/logout', [LoginController::class, 'logout'], ['auth']);

// ---- Admin (wajib login) ----
$router->add('GET', '/admin', [DashboardController::class, 'redirectToDashboard'], ['auth']);
$router->add('GET', '/admin/dashboard', [DashboardController::class, 'index'], ['auth']);

// Konten (draft)
$router->add('GET', '/admin/sections', [SectionController::class, 'index'], ['auth']);
$router->add('GET', '/admin/sections/{section}', [SectionController::class, 'show'], ['auth'], ['section' => $section]);
$router->add('PUT', '/admin/sections/{section}', [SectionController::class, 'update'], ['auth'], ['section' => $section]);
$router->add('POST', '/admin/sections/{section}/reset', [ResetController::class, 'section'], ['auth'], ['section' => $section]);
$router->add('POST', '/admin/reset-all', [ResetController::class, 'all'], ['auth']);

// Draft / publish
$router->add('GET', '/admin/publish/status', [PublishController::class, 'status'], ['auth']);
$router->add('POST', '/admin/publish', [PublishController::class, 'publish'], ['auth']);
$router->add('POST', '/admin/publish/discard', [PublishController::class, 'discard'], ['auth']);

// Upload (foto & lagu)
$router->add('POST', '/admin/media/image', [MediaController::class, 'image'], ['auth']);
$router->add('POST', '/admin/media/audio', [MediaController::class, 'audio'], ['auth']);

// Tamu & link personal
$router->add('POST', '/admin/guests/{guest}/regenerate', [GuestController::class, 'regenerate'], ['auth'], ['guest' => $id]);
$router->add('POST', '/admin/guests/{guest}/link', [GuestController::class, 'toggleLink'], ['auth'], ['guest' => $id]);
$router->add('GET', '/admin/guests', [GuestController::class, 'index'], ['auth']);
$router->add('POST', '/admin/guests', [GuestController::class, 'store'], ['auth']);
$router->add('GET', '/admin/guests/{guest}', [GuestController::class, 'show'], ['auth'], ['guest' => $id]);
$router->add('PUT|PATCH', '/admin/guests/{guest}', [GuestController::class, 'update'], ['auth'], ['guest' => $id]);
$router->add('DELETE', '/admin/guests/{guest}', [GuestController::class, 'destroy'], ['auth'], ['guest' => $id]);

$router->add('PATCH', '/admin/settings/online', [SettingController::class, 'online'], ['auth']);

// Preview (template yang sama dengan landing) & export
$router->add('GET', '/admin/preview', [PreviewController::class, 'full'], ['auth']);
$router->add('GET', '/admin/preview/frame', [PreviewController::class, 'frame'], ['auth']);
$router->add('POST', '/admin/preview/render', [PreviewController::class, 'render'], ['auth']);
$router->add('GET', '/admin/export/{type}', [ExportController::class, 'export'], ['auth'], ['type' => 'guests|rsvp|attendance|wishes']);

// RSVP & ucapan
$router->add('GET', '/admin/responses', [ResponseController::class, 'index'], ['auth']);
$router->add('PATCH', '/admin/responses/{response}/hidden', [ResponseController::class, 'setHidden'], ['auth'], ['response' => $id]);
$router->add('DELETE', '/admin/responses/{response}', [ResponseController::class, 'destroy'], ['auth'], ['response' => $id]);
