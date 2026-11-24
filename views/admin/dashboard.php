<?php
/*
 * Halaman Ringkasan: statistik tamu (dari database), status konten draft/published,
 * saklar undangan online/offline, dan daftar info penting yang belum diisi.
 */
$title = 'Ringkasan';
$heading = 'Ringkasan';
$page = 'dashboard';
$active = 'dashboard';
ob_start();
?>
<section class="cards" aria-label="Statistik tamu">
    <div class="stat"><b><?= e($stats['total_guests']) ?></b><span>Total tamu undangan</span></div>
    <div class="stat"><b><?= e($stats['responded']) ?></b><span>Sudah RSVP</span></div>
    <div class="stat ok"><b><?= e($stats['attending']) ?></b><span>Tamu hadir <small>(<?= e($stats['attending_people']) ?> orang)</small></span></div>
    <div class="stat no"><b><?= e($stats['not_attending']) ?></b><span>Tidak hadir</span></div>
    <div class="stat"><b><?= e($stats['pending']) ?></b><span>Belum konfirmasi</span></div>
    <div class="stat"><b><?= e($stats['wishes']) ?></b><span>Ucapan masuk</span></div>
</section>

<div class="two">
    <section class="panel">
        <h2>Status konten</h2>
        <p id="dash-status" class="muted">Memuat…</p>
        <div class="row"><a class="btn" href="<?= e(url('/admin/sections')) ?>">Kelola konten</a><a class="btn ghost" href="<?= e(url('/admin/preview')) ?>">Preview seluruh undangan</a></div>
        <hr>
        <label class="switch"><input type="checkbox" id="online-toggle" <?= $online ? 'checked' : '' ?>><span>Undangan online (dapat dibuka tamu)</span></label>
    </section>
    <section class="panel">
        <h2>Info yang perlu Anda isi</h2>
        <?php if (count($missing_info)): ?>
            <ul class="todo"><?php foreach ($missing_info as $path => $label): ?><li><a href="<?= e(url('/admin/sections/'.explode('.', $path)[0])) ?>"><?= e($label) ?></a></li><?php endforeach; ?></ul>
        <?php else: ?>
            <p class="muted">Semua info penting sudah terisi.</p>
        <?php endif; ?>
    </section>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
