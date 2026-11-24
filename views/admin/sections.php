<?php
/* Halaman "Konten undangan": kartu setiap section, ditandai bila masih ada info penting yang belum diisi. */
$title = 'Konten';
$heading = 'Konten undangan';
$page = 'sections';
$active = 'sections';
ob_start();
?>
<p class="muted">Setiap section punya halaman edit dengan Live Preview. Perubahan disimpan sebagai draft dan baru tampil ke tamu setelah Publish.</p>
<div class="secgrid">
    <?php foreach ($sections as $s): ?>
        <a class="seccard" href="<?= e(url('/admin/sections/'.$s['key'])) ?>">
            <b><?= e($s['label']) ?></b>
            <?php if (count($s['missing'])): ?><span class="tag warn"><?= e(count($s['missing'])) ?> info belum diisi</span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
