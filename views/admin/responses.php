<?php
/* Halaman "RSVP & ucapan": tabel respons tamu (diisi responses.js), filter, export, dan moderasi ucapan. */
$title = 'RSVP & ucapan';
$heading = 'RSVP & ucapan';
$page = 'responses';
$active = 'responses';
ob_start();
?>
<div class="toolbar">
    <input type="search" id="r-q" placeholder="Cari nama tamu…" aria-label="Cari tamu">
    <select id="r-att" aria-label="Filter kehadiran"><option value="">Semua status</option><option value="hadir">Hadir</option><option value="tidak_hadir">Tidak hadir</option></select>
    <select id="r-hid" aria-label="Filter tampil"><option value="">Semua ucapan</option><option value="0">Tampil</option><option value="1">Disembunyikan</option></select>
    <div class="grow"></div>
    <details class="menu"><summary class="btn ghost">Export</summary><div class="menu-box">
        <a data-export="rsvp" data-format="xlsx">Status RSVP (.xlsx)</a><a data-export="rsvp" data-format="csv">Status RSVP (.csv)</a><a data-export="wishes" data-format="xlsx">Ucapan (.xlsx)</a><a data-export="wishes" data-format="csv">Ucapan (.csv)</a>
    </div></details>
</div>
<p class="muted sm">Ucapan yang disembunyikan tidak tampil di undangan. Tamu yang ucapannya disembunyikan tetap tercatat RSVP-nya.</p>
<div class="tablewrap"><table class="tbl"><thead><tr><th>Tamu</th><th>Kehadiran</th><th>Ucapan</th><th>Waktu</th><th class="r">Aksi</th></tr></thead><tbody id="r-body"><tr><td colspan="5" class="muted">Memuat…</td></tr></tbody></table></div>
<div class="pager" id="r-pager"></div>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
