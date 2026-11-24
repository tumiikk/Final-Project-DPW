// Navbar undangan: submenu ACARA (Akad, Resepsi, Dresscode) dan penanda menu aktif sesuai bagian yang sedang dibaca.
const nav = document.getElementById('inv-nav');

if (nav) {
    const grp = nav.querySelector('.nv-grp');
    const btn = grp.querySelector('.nv-btn');
    const sub = grp.querySelector('.nv-sub');

    // ---- Submenu ACARA: buka/tutup ----
    const setOpen = (open) => {
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        sub.hidden = !open;
        grp.classList.toggle('open', open);
    };
    btn.addEventListener('click', (e) => { e.stopPropagation(); setOpen(sub.hidden); });
    sub.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('click', (e) => { if (!grp.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !sub.hidden) { setOpen(false); btn.focus(); } });

    // ---- Menu aktif: section => menu navbar ----
    const MAP = {
        couple_intro: 'mempelai', couple: 'mempelai', groom: 'mempelai',
        akad: 'acara', party: 'acara', resepsi: 'acara', dress_code: 'acara',
        gallery: 'galeri', gift: 'hadiah', rsvp: 'doa',
    };
    const items = [...nav.querySelectorAll('[data-nav]')];
    const setActive = (key) => items.forEach((el) => {
        const on = el.dataset.nav === key;
        el.classList.toggle('on', on);
        const target = el.matches('a') ? el : el.querySelector('.nv-btn');
        if (on) target.setAttribute('aria-current', 'true'); else target.removeAttribute('aria-current');
    });

    // Section yang melewati garis tengah layar dianggap sedang dibaca
    const sections = [...document.querySelectorAll('#invitation-body > section[data-section]')];
    const update = () => {
        const mid = window.innerHeight * 0.45;
        let current = null;
        for (const s of sections) {
            const r = s.getBoundingClientRect();
            if (r.top <= mid && r.bottom > mid) { current = s.id; break; }
        }
        setActive(MAP[current] || null);
    };
    let ticking = false;
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(() => { ticking = false; update(); }); } }, { passive: true });
    window.addEventListener('resize', update);
    update();
}

// ---- Kotak ucapan: tampilkan petunjuk "gulir" bila isi melebihi tinggi kotak ----
const wishList = document.querySelector('[data-wish-list]');
const wishHint = document.querySelector('[data-wish-hint]');
if (wishList && wishHint) {
    const check = () => { wishHint.hidden = wishList.scrollHeight <= wishList.clientHeight + 2; };
    check();
    window.addEventListener('resize', check);
    new MutationObserver(check).observe(wishList, { childList: true, subtree: true });
}
