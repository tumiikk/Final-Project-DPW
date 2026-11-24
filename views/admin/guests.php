<?php
/*
 * Halaman "Data tamu & link undangan": tabel tamu (diisi guests.js lewat JSON), pencarian & filter,
 * export, serta dialog tambah/edit tamu. Dialog & tombol QR Code sudah dihapus; link personal tetap.
 */
$title = 'Data tamu';
$heading = 'Data tamu & link undangan';
$page = 'guests';
$active = 'guests';
ob_start();
?>
<div class="toolbar">
    <input type="search" id="g-q" placeholder="Cari nama tamu…" aria-label="Cari tamu">
    <select id="g-status" aria-label="Filter RSVP"><option value="">Semua RSVP</option><option value="hadir">Hadir</option><option value="tidak_hadir">Tidak hadir</option><option value="belum">Belum konfirmasi</option></select>
    <select id="g-link" aria-label="Filter link"><option value="">Semua link</option><option value="active">Link aktif</option><option value="inactive">Link nonaktif</option></select>
    <div class="grow"></div>
    <details class="menu"><summary class="btn ghost">Export</summary><div class="menu-box">
        <a data-export="guests" data-format="xlsx">Daftar tamu (.xlsx)</a><a data-export="guests" data-format="csv">Daftar tamu (.csv)</a>
        <a data-export="rsvp" data-format="xlsx">Status RSVP (.xlsx)</a><a data-export="attendance" data-format="xlsx">Jumlah kehadiran (.xlsx)</a><a data-export="attendance" data-format="csv">Jumlah kehadiran (.csv)</a>
        <a data-export="wishes" data-format="xlsx">Ucapan tamu (.xlsx)</a><a data-export="wishes" data-format="csv">Ucapan tamu (.csv)</a>
    </div></details>
    <button type="button" class="btn" id="g-add">+ Tambah tamu</button>
</div>
<p class="muted sm">Export mengikuti pencarian/filter yang sedang aktif. Link personal dibagikan sendiri oleh Anda (salin link).</p>
<div class="tablewrap"><table class="tbl" id="g-table"><thead><tr><th>Nama</th><th>RSVP</th><th>Link</th><th class="r">Aksi</th></tr></thead><tbody id="g-body"><tr><td colspan="4" class="muted">Memuat…</td></tr></tbody></table></div>
<div class="pager" id="g-pager"></div>

<dialog id="g-dlg" class="dlg"><form id="g-form"><h3 id="g-dlg-t">Tambah tamu</h3>
    <label>Nama tamu<input name="name" required maxlength="150" autocomplete="off"></label>
    <label>Maksimal orang yang boleh hadir<input name="max_attendees" type="number" min="1" max="20" value="1"></label>
    <label>Catatan (opsional, hanya untuk admin)<input name="notes" maxlength="255"></label>
    <p class="err" id="g-err" role="alert"></p>
    <div class="row end"><button type="button" class="btn ghost" id="g-cancel">Batal</button><button type="submit" class="btn">Simpan</button></div></form></dialog>
<?php
$content = ob_get_clean();
require BASE_PATH.'/views/layouts/admin.php';
