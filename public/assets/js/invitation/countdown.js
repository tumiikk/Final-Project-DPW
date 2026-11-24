// Hitung mundur ke acara. Tanggal dari data acara (ISO dengan offset zona waktu), bukan hardcode.
document.querySelectorAll('[data-countdown]').forEach((box) => {
    const iso = box.dataset.countdown;
    const target = iso ? Date.parse(iso) : NaN;
    if (Number.isNaN(target)) return;

    const cell = (k) => box.querySelector(`[data-cd="${k}"]`);
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
        let s = Math.max(0, Math.floor((target - Date.now()) / 1000));
        const d = Math.floor(s / 86400); s -= d * 86400;
        const h = Math.floor(s / 3600); s -= h * 3600;
        const m = Math.floor(s / 60); s -= m * 60;
        cell('d').textContent = String(d).padStart(2, '0');
        cell('h').textContent = pad(h);
        cell('m').textContent = pad(m);
        cell('s').textContent = pad(s);
    };
    tick();
    setInterval(tick, 1000);
});
