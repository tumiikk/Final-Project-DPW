// Halaman RSVP & ucapan: daftar respons, filter, sembunyikan/tampilkan ucapan, hapus respons, export.
import { api, $, esc, toast, firstError, confirmDlg, appUrl } from './common.js';

let page = 1; let timer;
const fmt = (iso) => new Date(iso).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

export function initResponses() {
    const q = () => ({ q: $('#r-q').value.trim(), attendance: $('#r-att').value, hidden: $('#r-hid').value });
    const load = async (p = 1) => {
        page = p;
        const params = new URLSearchParams(Object.entries({ ...q(), page: p }).filter(([, v]) => v !== ''));
        const r = await api('/admin/responses?' + params);
        if (!r.ok) { $('#r-body').innerHTML = '<tr><td colspan="5" class="muted">Gagal memuat data.</td></tr>'; return; }
        const d = r.data;
        $('#r-body').innerHTML = d.data.length ? d.data.map(row).join('') : '<tr><td colspan="5" class="muted">Belum ada respons yang cocok.</td></tr>';
        $('#r-pager').innerHTML = d.last_page > 1 ? `<button class="btn ghost sm" ${d.current_page <= 1 ? 'disabled' : ''} data-p="${d.current_page - 1}">‹</button><span class="muted sm">Halaman ${d.current_page} / ${d.last_page} · ${d.total} respons</span><button class="btn ghost sm" ${d.current_page >= d.last_page ? 'disabled' : ''} data-p="${d.current_page + 1}">›</button>` : `<span class="muted sm">${d.total} respons</span>`;
    };
    const row = (r) => `<tr data-id="${r.id}">
        <td><b>${esc(r.guest_name)}</b></td>
        <td><span class="chip ${r.attendance === 'hadir' ? 'ok' : 'no'}">${r.attendance === 'hadir' ? 'Hadir' : 'Tidak hadir'}</span>${r.attendance === 'hadir' ? ' · ' + r.attendee_count + ' orang' : ''}</td>
        <td>${r.message ? `<div class="wishtxt">${esc(r.message)}</div>` : '<span class="muted">—</span>'}${r.is_hidden ? ' <span class="chip warn">Disembunyikan</span>' : ''}</td>
        <td class="muted sm">${fmt(r.submitted_at)}</td>
        <td class="r"><div class="acts">${r.message ? `<button class="btn ghost sm" data-a="hide" data-hidden="${r.is_hidden ? 1 : 0}">${r.is_hidden ? 'Tampilkan' : 'Sembunyikan'}</button>` : ''}<button class="btn ghost sm" data-a="del" style="color:var(--bad)">Hapus</button></div></td></tr>`;

    ['#r-att', '#r-hid'].forEach((s) => $(s).addEventListener('change', () => load(1)));
    $('#r-q').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(1), 300); });
    $('#r-pager').addEventListener('click', (e) => { const b = e.target.closest('[data-p]'); if (b && !b.disabled) load(+b.dataset.p); });
    document.querySelectorAll('[data-export]').forEach((a) => a.addEventListener('click', () => {
        const f = q(); const params = new URLSearchParams({ format: a.dataset.format, ...(f.q ? { q: f.q } : {}), ...(f.attendance ? { status: f.attendance } : {}) });
        location.href = appUrl(`/admin/export/${a.dataset.export}?`) + params;
    }));
    $('#r-body').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-a]'); if (!b) return; const id = b.closest('tr').dataset.id;
        if (b.dataset.a === 'hide') {
            const hide = b.dataset.hidden !== '1';
            const r = await api(`/admin/responses/${id}/hidden`, { method: 'PATCH', body: { hidden: hide } });
            if (r.ok) { toast(hide ? 'Ucapan disembunyikan dari undangan.' : 'Ucapan ditampilkan kembali.', 'ok'); load(page); } else toast(firstError(r.data), 'err');
        } else if (b.dataset.a === 'del') {
            if (!(await confirmDlg('Hapus respons?', 'RSVP dan ucapan tamu ini dihapus. Tamu kembali berstatus “belum konfirmasi”.', 'Hapus', true))) return;
            const r = await api('/admin/responses/' + id, { method: 'DELETE' });
            if (r.ok) { toast('Respons dihapus.', 'ok'); load(page); } else toast(firstError(r.data), 'err');
        }
    });
    load(1);
}
