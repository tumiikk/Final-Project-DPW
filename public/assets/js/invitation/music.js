// Musik latar: tidak autoplay. Mulai hanya setelah interaksi pengguna (tombol buka undangan / tombol musik).
const audio = document.getElementById('bgm');
const btn = document.getElementById('music-btn');

if (audio && btn) {
    const sync = () => {
        const playing = !audio.paused;
        btn.classList.toggle('playing', playing);
        btn.setAttribute('aria-pressed', String(playing));
        btn.setAttribute('aria-label', playing ? 'Jeda musik latar' : 'Putar musik latar');
    };
    const show = () => btn.classList.add('show');

    btn.addEventListener('click', () => {
        if (audio.paused) audio.play().catch(() => {}); else audio.pause();
    });
    audio.addEventListener('play', sync);
    audio.addEventListener('pause', sync);

    if (document.querySelector('.cover-wrap.gate')) {
        // Pengunjung menekan "Buka Undangan" => itu interaksi yang sah untuk memulai musik.
        document.addEventListener('invitation:open', () => { show(); audio.play().catch(() => {}); });
    } else {
        show();
    }
}
