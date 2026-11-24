// RSVP & ucapan: kirim via fetch (tanpa reload). Identitas tamu selalu dari token di URL server.
// Ucapan baru langsung muncul paling atas di kotak ucapan yang dapat digulir; tombol salin dipakai section Kirim Hadiah.
const form = document.querySelector('[data-rsvp-form]');

if (form) {
    const att = form.querySelector('[name="attendance"]');
    const countBox = form.querySelector('[data-count-box]');
    const msg = form.querySelector('[data-rs-msg]');
    const btn = form.querySelector('button[type="submit"]');
    const say = (text, kind) => { msg.textContent = text; msg.className = 'rs-msg ' + (kind || ''); };

    const toggleCount = () => countBox.classList.toggle('on', att.value === 'hadir');
    att.addEventListener('change', toggleCount);
    toggleCount();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!att.value) { say('Pilih konfirmasi kehadiran.', 'err'); att.focus(); return; }
        btn.disabled = true;
        say('Mengirim…');
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: new FormData(form),
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 422) {
                const first = Object.values(data.errors || {})[0];
                say((first && first[0]) || 'Periksa kembali isian Anda.', 'err');
            } else if (res.status === 429) {
                say('Terlalu banyak percobaan. Coba lagi sebentar lagi.', 'err');
            } else if (!res.ok || !data.ok) {
                say('Maaf, terjadi kendala. Silakan coba lagi.', 'err');
            } else {
                say(msg.dataset.ok || 'Terima kasih.', 'ok');
                if (data.wish) upsertWish(data.wish);
            }
        } catch {
            say('Koneksi bermasalah. Silakan coba lagi.', 'err');
        } finally {
            btn.disabled = false;
        }
    });
}

function upsertWish(w) {
    const wrap = document.getElementById('wishes');
    if (!wrap) return;
    const box = wrap.querySelector('[data-wish-list]') || wrap; // daftar ucapan yang dapat digulir
    // hapus ucapan lama milik tamu yang sama (satu tamu = satu respons)
    box.querySelectorAll(`[data-wish][data-key="${w.key}"]`).forEach((n) => n.remove());
    const el = document.createElement('div');
    el.className = 'wish';
    el.dataset.wish = '';
    el.dataset.key = w.key;
    const name = document.createElement('span'); name.className = 'wn'; name.textContent = w.name;
    const p = document.createElement('p'); p.textContent = w.message;
    el.append(name, p);
    const first = box.querySelector('.wish');
    const empty = box.querySelector('.wish:not([data-wish])');
    if (empty) empty.remove();
    box.insertBefore(el, box.querySelector('.wish'));
    box.scrollTop = 0; // tampilkan ucapan terbaru
}

document.querySelectorAll('[data-wish-more]').forEach((b) => b.addEventListener('click', () => {
    document.querySelectorAll('.wish.more[hidden]').forEach((n) => n.removeAttribute('hidden'));
    b.remove();
}));

// Tombol salin nomor rekening. Cadangan execCommand dipakai bila Clipboard API tidak tersedia (mis. http lokal).
async function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) { await navigator.clipboard.writeText(text); return; }
    const ta = document.createElement('textarea');
    ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.append(ta); ta.select();
    const ok = document.execCommand('copy'); ta.remove();
    if (!ok) throw new Error('copy');
}
document.querySelectorAll('[data-copy]').forEach((b) => b.addEventListener('click', async () => {
    const label = b.querySelector('span') || b; // label teks (ikon tidak ikut diganti)
    try {
        await copyText(b.dataset.copy);
        const t = label.textContent; label.textContent = 'Tersalin'; b.classList.add('done');
        setTimeout(() => { label.textContent = t; b.classList.remove('done'); }, 1500);
    } catch { /* abaikan */ }
}));
