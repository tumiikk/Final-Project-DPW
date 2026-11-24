// Halaman Ringkasan: saklar undangan online/offline.
import { api, $, toast, firstError } from './common.js';

export function initDashboard() {
    const t = $('#online-toggle');
    t?.addEventListener('change', async () => {
        const r = await api('/admin/settings/online', { method: 'PATCH', body: { online: t.checked } });
        if (r.ok) toast(t.checked ? 'Undangan online.' : 'Undangan dimatikan sementara.', 'ok');
        else { t.checked = !t.checked; toast(firstError(r.data), 'err'); }
    });
}
