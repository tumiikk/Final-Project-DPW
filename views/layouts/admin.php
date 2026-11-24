<?php
/*
 * Layout dashboard admin (pengganti layouts/admin.blade.php): sidebar menu, header + tombol Publish,
 * area konten, toast, dan dialog konfirmasi.
 * Setiap halaman admin mengisi: $title, $heading, $page (nama halaman untuk admin.js), $active (menu aktif), $content (HTML).
 */
$nav = fn (string $key) => ($active ?? '') === $key ? 'on' : '';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-base" content="<?= e(base_path_url()) ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Dashboard') ?> · Admin Undangan</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/admin.css')) ?>">
    <script type="module" src="<?= e(asset_v('assets/js/admin.js')) ?>"></script>
</head>
<body class="adm" data-page="<?= e($page ?? '') ?>">
<div class="shell">
    <aside class="side" id="side" aria-label="Menu utama">
        <a class="brand" href="<?= e(url('/admin/dashboard')) ?>"><span class="script">Ayu &amp; Kevin</span><small>Panel Undangan</small></a>
        <nav>
            <a href="<?= e(url('/admin/dashboard')) ?>" class="<?= $nav('dashboard') ?>">Ringkasan</a>
            <div class="grp">Konten undangan</div>
            <a href="<?= e(url('/admin/sections')) ?>" class="<?= $nav('sections') ?>">Semua section</a>
            <a href="<?= e(url('/admin/preview')) ?>" class="<?= $nav('preview') ?>">Preview seluruh undangan</a>
            <div class="grp">Tamu</div>
            <a href="<?= e(url('/admin/guests')) ?>" class="<?= $nav('guests') ?>">Data tamu &amp; link</a>
            <a href="<?= e(url('/admin/responses')) ?>" class="<?= $nav('responses') ?>">RSVP &amp; ucapan</a>
        </nav>
        <form method="POST" action="<?= e(url('/admin/logout')) ?>" class="out"><?= csrf_field() ?><button type="submit">Keluar</button></form>
    </aside>
    <div class="main">
        <header class="top">
            <button type="button" class="burger" id="burger" aria-label="Buka menu" aria-controls="side" aria-expanded="false">☰</button>
            <h1><?= e($heading ?? 'Dashboard') ?></h1>
            <div class="top-r">
                <span class="pill" id="status-pill" data-state="loading">…</span>
                <button type="button" class="btn gold" id="btn-publish" disabled>Publish</button>
            </div>
        </header>
        <main class="content"><?= $content ?></main>
    </div>
</div>
<div class="toasts" id="toasts" aria-live="polite"></div>
<dialog id="dlg" class="dlg"><form method="dialog"><h3 id="dlg-t"></h3><p id="dlg-m"></p><div class="row end"><button value="cancel" class="btn ghost">Batal</button><button value="ok" class="btn" id="dlg-ok">Lanjut</button></div></form></dialog>
</body>
</html>
