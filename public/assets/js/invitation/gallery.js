// Lightbox galeri: klik foto untuk memperbesar, panah/ESC di keyboard.
const lb = document.getElementById('lightbox');
const dataEl = document.getElementById('gallery-data');

if (lb && dataEl) {
    const items = JSON.parse(dataEl.textContent);
    const img = lb.querySelector('img');
    const cap = lb.querySelector('p');
    let i = 0;
    const show = (n) => {
        i = (n + items.length) % items.length;
        img.src = items[i].src;
        img.alt = items[i].caption || 'Foto galeri';
        cap.textContent = items[i].caption || '';
        cap.hidden = !items[i].caption;
    };
    const open = (n) => { show(n); lb.classList.add('show'); lb.querySelector('.lb-x').focus(); };
    const close = () => lb.classList.remove('show');

    document.querySelectorAll('[data-gal]').forEach((el) => el.addEventListener('click', () => open(Number(el.dataset.gal))));
    lb.querySelector('.lb-x').addEventListener('click', close);
    lb.querySelector('.lb-p').addEventListener('click', () => show(i - 1));
    lb.querySelector('.lb-n').addEventListener('click', () => show(i + 1));
    lb.addEventListener('click', (e) => { if (e.target === lb) close(); });
    document.addEventListener('keydown', (e) => {
        if (!lb.classList.contains('show')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') show(i - 1);
        if (e.key === 'ArrowRight') show(i + 1);
    });
}
