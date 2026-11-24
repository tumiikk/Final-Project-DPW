<?php
/* Halaman "Preview seluruh undangan" (versi draft) dengan pilihan tampilan Desktop / Mobile. */
$title = 'Preview undangan';
$heading = 'Preview seluruh undangan';
$page = 'preview';
$active = 'preview';
ob_start();
?>
<div class="prev-bar"><div class="seg" role="group" aria-label="Mode preview"><button type="button" class="on" data-mode="desktop">Desktop</button><button type="button" data-mode="mobile">Mobile</button></div>
<span class="muted sm">Menampilkan draft terbaru (termasuk perubahan yang belum dipublikasikan). Tamu tetap melihat versi published.</span></div>
<div class="prev-view tall" id="prev-view"><div class="prev-stage" id="prev-stage"><iframe id="prev-frame" title="Preview seluruh undangan" src="<?= e(url('/admin/preview/frame').'?full=1') ?>"></iframe></div></div>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
