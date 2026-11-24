// Gerbang cover: isi undangan terkunci sampai tombol "Buka Undangan" ditekan.
const gate = document.querySelector('.cover-wrap.gate');
const body = document.body;

if (gate) {
    const btn = document.getElementById('open-invitation');
    btn?.addEventListener('click', () => {
        gate.classList.add('open');
        body.classList.remove('locked');
        window.scrollTo(0, 0);
        document.dispatchEvent(new CustomEvent('invitation:open'));
        setTimeout(() => { gate.hidden = true; }, 1000);
        const hash = location.hash && document.querySelector(location.hash);
        if (hash) hash.scrollIntoView();
    });
} else {
    body.classList.remove('locked');
}
