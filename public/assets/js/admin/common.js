// Fungsi bersama dashboard admin: request JSON + CSRF, toast, dialog konfirmasi, status Publish, menu samping.
// (Migrasi PHP Native: ditambah appUrl agar dashboard tetap berjalan bila website dibuka dari subfolder.)
export const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

// Prefix folder website (kosong bila website berada di root domain), dibaca dari <meta name="app-base">.
export const base = document.querySelector('meta[name="app-base"]')?.content || '';
/** Menambahkan prefix folder pada path "/admin/..." (path yang sudah berprefix tidak diubah). */
export const appUrl = (path) => (base && path.startsWith('/') && !path.startsWith(base + '/') ? base + path : path);
export const $ = (s, r = document) => r.querySelector(s);
export const $$ = (s, r = document) => [...r.querySelectorAll(s)];
export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/** fetch JSON dengan CSRF. Mengembalikan { ok, status, data }. */
export async function api(url, { method = 'GET', body = null, form = null } = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' };
    let payload;
    if (form) payload = form;
    else if (body !== null) { headers['Content-Type'] = 'application/json'; payload = JSON.stringify(body); }
    try {
        const res = await fetch(appUrl(url), { method, headers, body: payload, credentials: 'same-origin' });
        if (res.status === 401 || res.status === 419) { toast('Sesi berakhir. Muat ulang halaman dan login kembali.', 'err'); }
        let data = null;
        if (res.status !== 204) data = await res.json().catch(() => null);
        return { ok: res.ok, status: res.status, data };
    } catch {
        toast('Koneksi bermasalah. Periksa jaringan Anda.', 'err');
        return { ok: false, status: 0, data: null };
    }
}

export function firstError(data, fallback = 'Terjadi kesalahan. Silakan coba lagi.') {
    if (data?.errors) { const f = Object.values(data.errors)[0]; if (f?.[0]) return f[0]; }
    return data?.message || fallback;
}

export function toast(msg, kind = '') {
    const box = $('#toasts'); if (!box) return;
    const el = document.createElement('div'); el.className = 'toast ' + kind; el.textContent = msg; box.append(el);
    setTimeout(() => el.remove(), kind === 'err' ? 6000 : 3200);
}

export function confirmDlg(title, message, okLabel = 'Lanjut', danger = false) {
    const d = $('#dlg');
    $('#dlg-t').textContent = title; $('#dlg-m').textContent = message;
    const ok = $('#dlg-ok'); ok.textContent = okLabel; ok.classList.toggle('danger', danger);
    return new Promise((resolve) => {
        d.addEventListener('close', () => resolve(d.returnValue === 'ok'), { once: true });
        d.returnValue = 'cancel'; d.showModal();
    });
}

const STATE_LABEL = { draft: 'Draft · belum dipublikasikan', draft_ahead: 'Draft · ada perubahan belum dipublikasikan', published: 'Published' };
export function applyStatus(s) {
    if (!s) return;
    const pill = $('#status-pill'); const btn = $('#btn-publish');
    if (pill) { pill.dataset.state = s.state; pill.textContent = STATE_LABEL[s.state] || s.state; }
    if (btn) btn.disabled = !s.has_unpublished_changes;
    const dash = $('#dash-status');
    if (dash) dash.textContent = s.state === 'published' ? 'Undangan yang dilihat tamu sama dengan draft terbaru.' : 'Ada perubahan draft yang belum dipublikasikan. Tamu masih melihat versi published sebelumnya.';
}
export async function refreshStatus() { const r = await api('/admin/publish/status'); if (r.ok) applyStatus(r.data); }

export function initChrome() {
    const side = $('#side'); const burger = $('#burger');
    burger?.addEventListener('click', () => { const o = side.classList.toggle('open'); burger.setAttribute('aria-expanded', String(o)); });
    document.addEventListener('click', (e) => { if (side?.classList.contains('open') && !e.target.closest('#side, #burger')) side.classList.remove('open'); });
    if (!$('#status-pill')) return;
    refreshStatus();
    $('#btn-publish')?.addEventListener('click', async () => {
        if (window.__edDirty) { toast('Ada perubahan yang belum disimpan. Simpan draft dulu sebelum Publish.', 'err'); return; }
        if (!(await confirmDlg('Publish perubahan?', 'Versi draft akan menggantikan versi yang dilihat tamu sekarang.', 'Publish'))) return;
        const r = await api('/admin/publish', { method: 'POST' });
        if (r.ok) { applyStatus(r.data); toast('Undangan berhasil dipublikasikan.', 'ok'); } else toast(firstError(r.data), 'err');
    });
    // tutup menu export saat klik di luar
    document.addEventListener('click', (e) => { $$('details.menu[open]').forEach((d) => { if (!d.contains(e.target)) d.removeAttribute('open'); }); });
}
