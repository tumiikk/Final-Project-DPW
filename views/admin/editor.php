<?php
/*
 * Halaman editor satu section: form (dibangun editor.js dari deskriptor AdminFields) + Live Preview iframe,
 * tombol Simpan draft / Batalkan / Reset section, dialog crop foto, dan pustaka foto default.
 * Semua URL endpoint dikirim lewat atribut data-* agar JavaScript tidak perlu menebak alamat.
 */
$title = $label;
$heading = $label;
$page = 'editor';
$active = 'sections';
// Data awal editor (draft, default, deskriptor form, daftar foto default) dalam bentuk JSON
$boot = ['data' => $data, 'default' => $default, 'fields' => $fields, 'library' => $library];
ob_start();
?>
<div class="ed" id="editor"
     data-section="<?= e($key) ?>" data-version="<?= e($version) ?>"
     data-url-save="<?= e(url('/admin/sections/'.$key)) ?>" data-url-reset="<?= e(url('/admin/sections/'.$key.'/reset')) ?>"
     data-url-render="<?= e(url('/admin/preview/render')) ?>" data-url-frame="<?= e(url('/admin/preview/frame')) ?>"
     data-url-image="<?= e(url('/admin/media/image')) ?>" data-url-audio="<?= e(url('/admin/media/audio')) ?>" data-url-discard="<?= e(url('/admin/publish/discard')) ?>"
     data-url-full="<?= e(url('/admin/preview')) ?>" data-base-default="<?= e(asset('images/default')) ?>" data-base-upload="<?= e(url('storage')) ?>/">
    <script type="application/json" id="ed-boot"><?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

    <div class="ed-bar">
        <a class="btn ghost sm" href="<?= e(url('/admin/sections')) ?>">← Semua section</a>
        <span class="pill" id="dirty-pill" data-state="clean">Tersimpan</span>
        <div class="grow"></div>
        <button type="button" class="btn ghost sm" id="btn-cancel" disabled>Batalkan perubahan</button>
        <button type="button" class="btn ghost sm" id="btn-reset">Reset section</button>
        <a class="btn ghost sm" href="<?= e(url('/admin/preview')) ?>" target="_blank" rel="noopener" id="btn-full">Preview seluruh undangan</a>
        <button type="button" class="btn sm" id="btn-save" disabled>Simpan draft</button>
    </div>

    <div class="ed-tabs" role="tablist"><button role="tab" aria-selected="true" data-tab="form">Form</button><button role="tab" aria-selected="false" data-tab="preview">Preview</button></div>

    <div class="ed-grid" data-show="form">
        <section class="ed-form" id="ed-form" aria-label="Form pengeditan"></section>
        <aside class="ed-prev" aria-label="Live Preview">
            <div class="prev-bar">
                <div class="seg" role="group" aria-label="Mode preview"><button type="button" class="on" data-mode="desktop">Desktop</button><button type="button" data-mode="mobile">Mobile</button></div>
                <span class="muted sm" id="prev-state"></span>
            </div>
            <div class="prev-view" id="prev-view"><div class="prev-stage" id="prev-stage"><iframe id="prev-frame" title="Live Preview" src="<?= e(url('/admin/preview/frame')) ?>"></iframe></div></div>
            <p class="muted sm" id="prev-none" hidden>Bagian ini tidak punya tampilan visual di undangan.</p>
        </aside>
    </div>
</div>

<dialog id="crop-dlg" class="dlg wide">
    <div class="crop"><h3>Atur crop &amp; posisi foto</h3>
        <div class="crop-box" id="crop-box" tabindex="0" aria-label="Geser foto untuk mengatur posisi"><img id="crop-img" alt="" draggable="false"></div>
        <label class="range">Perbesar <input type="range" id="crop-zoom" min="1" max="4" step="0.01" value="1"></label>
        <p class="muted sm">Geser foto di dalam bingkai. Bingkai memakai rasio yang sama dengan slot di undangan, jadi hasilnya sama dengan tampilan akhir.</p>
        <div class="row end"><button type="button" class="btn ghost" id="crop-reset">Posisi awal</button><button type="button" class="btn ghost" id="crop-cancel">Batal</button><button type="button" class="btn" id="crop-ok">Terapkan</button></div>
    </div>
</dialog>
<dialog id="lib-dlg" class="dlg wide"><div class="lib"><h3>Pilih foto default</h3><div class="libgrid" id="lib-grid"></div><div class="row end"><button type="button" class="btn ghost" id="lib-close">Tutup</button></div></div></dialog>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
