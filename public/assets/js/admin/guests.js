// Halaman Data tamu & link: daftar, cari/filter, tambah/edit, salin link, aktif/nonaktif, buat ulang link, hapus, export.
// (Migrasi PHP Native: tombol & dialog QR Code dihapus sesuai permintaan; link personal tetap.)
import { api, $, esc, toast, firstError, confirmDlg, appUrl } from './common.js';

const STATUS = { hadir: ['Hadir', 'ok'], tidak_hadir: ['Tidak hadir', 'no'], belum: ['Belum konfirmasi', ''] };
let page = 1; let timer;

export function initGuests() {
    const q = () => ({ q: $('#g-q').value.trim(), status: $('#g-status').value, link: $('#g-link').value });
    const load = async (p = 1) => {
        page = p;
        const params = new URLSearchParams(Object.entries({ ...q(), page: p }).filter(([, v]) => v !== '' && v !== null));
        const r = await api('/admin/guests?' + params);
        if (!r.ok) { $('#g-body').innerHTML = '<tr><td colspan="4" class="muted">Gagal memuat data.</td></tr>'; return; }
        render(r.data);
    };
    const render = (d) => {
        const body = $('#g-body');
        if (!d.data.length) body.innerHTML = '<tr><td colspan="4" class="muted">Belum ada tamu yang cocok. Tambahkan tamu dengan tombol “Tambah tamu”.</td></tr>';
        else body.innerHTML = d.data.map(row).join('');
        $('#g-pager').innerHTML = d.last_page > 1 ? `<button class="btn ghost sm" ${d.current_page <= 1 ? 'disabled' : ''} data-p="${d.current_page - 1}">‹ Sebelumnya</button><span class="muted sm">Halaman ${d.current_page} / ${d.last_page} · ${d.total} tamu</span><button class="btn ghost sm" ${d.current_page >= d.last_page ? 'disabled' : ''} data-p="${d.current_page + 1}">Berikutnya ›</button>` : `<span class="muted sm">${d.total} tamu</span>`;
    };
    const row = (g) => {
        const [lab, cls] = STATUS[g.rsvp_status] || STATUS.belum;
        const extra = g.rsvp_status === 'hadir' ? ` · ${g.attendee_count} orang` : '';
        return `<tr data-id="${g.id}">
          <td><b>${esc(g.name)}</b><div class="muted sm">maks. ${g.max_attendees} orang${g.notes ? ' · ' + esc(g.notes) : ''}</div></td>
          <td><span class="chip ${cls}">${lab}</span>${extra}${g.message ? `<div class="muted sm wishtxt">“${esc(g.message)}”</div>` : ''}</td>
          <td>${g.is_link_active ? '<span class="chip ok">Aktif</span>' : '<span class="chip no">Nonaktif</span>'}<div class="muted sm" style="word-break:break-all;max-width:260px">${esc(g.url)}</div></td>
          <td class="r"><div class="acts">
            <button class="btn ghost sm" data-a="copy" data-url="${esc(g.url)}">Salin link</button>
            <button class="btn ghost sm" data-a="edit">Edit</button>
            <button class="btn ghost sm" data-a="toggle" data-active="${g.is_link_active ? 1 : 0}">${g.is_link_active ? 'Nonaktifkan' : 'Aktifkan'}</button>
            <button class="btn ghost sm" data-a="regen">Buat ulang link</button>
            <button class="btn ghost sm" data-a="del" style="color:var(--bad)">Hapus</button></div></td></tr>`;
    };

    ['#g-status', '#g-link'].forEach((s) => $(s).addEventListener('change', () => load(1)));
    $('#g-q').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(1), 300); });
    $('#g-pager').addEventListener('click', (e) => { const b = e.target.closest('[data-p]'); if (b && !b.disabled) load(+b.dataset.p); });

    // export mengikuti filter aktif
    document.querySelectorAll('[data-export]').forEach((a) => a.addEventListener('click', () => {
        const params = new URLSearchParams(Object.entries({ ...q(), format: a.dataset.format }).filter(([, v]) => v));
        location.href = appUrl(`/admin/export/${a.dataset.export}?`) + params;
    }));

    // tambah / edit
    const dlg = $('#g-dlg'); const form = $('#g-form'); let editing = null;
    const open = (g) => { editing = g; $('#g-dlg-t').textContent = g ? 'Edit tamu' : 'Tambah tamu'; form.name.value = g?.name || ''; form.max_attendees.value = g?.max_attendees || 1; form.notes.value = g?.notes || ''; $('#g-err').textContent = ''; dlg.showModal(); form.name.focus(); };
    $('#g-add').addEventListener('click', () => open(null));
    $('#g-cancel').addEventListener('click', () => dlg.close());
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = { name: form.name.value.trim(), max_attendees: +form.max_attendees.value || 1, notes: form.notes.value.trim() || null };
        const r = editing ? await api('/admin/guests/' + editing.id, { method: 'PUT', body }) : await api('/admin/guests', { method: 'POST', body });
        if (!r.ok) { $('#g-err').textContent = firstError(r.data); return; }
        dlg.close(); toast(editing ? 'Data tamu diperbarui.' : 'Tamu ditambahkan, link personal dibuat.', 'ok'); load(page);
    });

    $('#g-body').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-a]'); if (!b) return;
        const id = b.closest('tr').dataset.id; const a = b.dataset.a;
        if (a === 'copy') {
            try { await navigator.clipboard.writeText(b.dataset.url); toast('Link disalin.', 'ok'); }
            catch { window.prompt('Salin link berikut:', b.dataset.url); }
        } else if (a === 'edit') {
            const r = await api('/admin/guests/' + id); if (r.ok) open(r.data);
        } else if (a === 'toggle') {
            const active = b.dataset.active === '1';
            if (active && !(await confirmDlg('Nonaktifkan link?', 'Tamu tidak bisa membuka undangan lewat link ini sampai diaktifkan lagi. Data tamu tidak dihapus.', 'Nonaktifkan'))) return;
            const r = await api(`/admin/guests/${id}/link`, { method: 'POST', body: { active: !active } });
            if (r.ok) { toast(active ? 'Link dinonaktifkan.' : 'Link diaktifkan.', 'ok'); load(page); } else toast(firstError(r.data), 'err');
        } else if (a === 'regen') {
            if (!(await confirmDlg('Buat ulang link?', 'Link lama akan dinonaktifkan dan tidak bisa dipakai lagi. Link baru dibuat, bagikan ulang ke tamu ini.', 'Buat ulang', true))) return;
            const r = await api(`/admin/guests/${id}/regenerate`, { method: 'POST' });
            if (r.ok) { toast('Link baru dibuat. Link lama sudah tidak berlaku.', 'ok'); load(page); } else toast(firstError(r.data), 'err');
        } else if (a === 'del') {
            if (!(await confirmDlg('Hapus tamu?', 'Tamu beserta RSVP dan ucapannya akan dihapus permanen. Link-nya tidak akan bisa dibuka lagi.', 'Hapus', true))) return;
            const r = await api('/admin/guests/' + id, { method: 'DELETE' });
            if (r.ok) { toast('Tamu dihapus.', 'ok'); load(page); } else toast(firstError(r.data), 'err');
        }
    });
    load(1);
}
