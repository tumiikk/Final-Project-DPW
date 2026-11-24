<?php
/*
 * Halaman undangan publik (landing & link personal tamu).
 * Memuat CSS/JS undangan, isi undangan (page.php), tombol musik (bila diaktifkan), dan lightbox galeri.
 */
$media = new App\Services\MediaService();
$music = $payload['music'];
// Musik hanya dimuat bila diaktifkan admin (tidak autoplay; diputar setelah tamu menekan tombol)
$musicUrl = ($music['enabled'] ?? false) ? $media->url($music['file'] ?? null, 'audio') : null;
$cover = $payload['cover'];
$title = $cover['bride_name'].' & '.$cover['groom_name'];
$og = $media->url($cover['background'] ?? null);

// Navbar: menu HADIAH dan pintasan Dresscode hanya muncul bila section-nya tampil di undangan
$navGift = App\Support\Inv::giftVisible($payload);
$navDress = App\Support\Inv::dressVisible($payload);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="noindex, nofollow">
    <title>The Wedding of <?= e($title) ?></title>
    <meta property="og:title" content="The Wedding of <?= e($title) ?>">
    <meta property="og:image" content="<?= e(str_starts_with($og, 'http') ? $og : absolute_url(substr($og, strlen(base_path_url())))) ?>">
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/invitation.css')) ?>">
    <script type="module" src="<?= e(asset_v('assets/js/invitation.js')) ?>"></script>
</head>
<body class="inv locked has-nav">
    <?= view('invitation.page', ['payload' => $payload, 'guest' => $guest, 'wishes' => $wishes, 'preview' => $preview]) ?>
    <?php /* NAVBAR: vertikal di kanan (desktop), horizontal di bawah (mobile). Muncul setelah undangan dibuka. */ ?>
    <nav class="inv-nav" id="inv-nav" aria-label="Navigasi undangan">
        <a href="#couple_intro" data-nav="mempelai">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="14" r="5.2"/><circle cx="15" cy="14" r="5.2"/><path d="M12 3.2l1.6 1.9-1.6 1.7-1.6-1.7z"/></svg><span>MEMPELAI</span>
        </a>
        <div class="nv-grp" data-nav="acara">
            <button type="button" class="nv-btn" aria-expanded="false" aria-controls="nv-sub" aria-haspopup="true">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M8 3v4M16 3v4M12 13.2l.9 1.8 2 .3-1.45 1.4.35 2-1.8-.95-1.8.95.35-2-1.45-1.4 2-.3z"/></svg><span>ACARA</span>
            </button>
            <div class="nv-sub" id="nv-sub" hidden>
                <a href="#akad"><?= e(($payload['akad']['title'] ?? '') ?: 'Akad Nikah') ?></a>
                <a href="#resepsi"><?= e(($payload['resepsi']['title'] ?? '') ?: 'Resepsi') ?></a>
                <?php if ($navDress): ?><a href="#dress_code"><?= e(($payload['dress_code']['title'] ?? '') ?: 'Dresscode') ?></a><?php endif; ?>
            </div>
        </div>
        <a href="#gallery" data-nav="galeri">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="1.5"/><circle cx="9" cy="9.5" r="1.7"/><path d="M3.5 17l5-4.5 3.5 3 3-2.5 5.5 4.5"/></svg><span>GALERI</span>
        </a>
        <?php if ($navGift): ?>
        <a href="#gift" data-nav="hadiah">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="9" width="17" height="11" rx="1"/><path d="M2.5 9h19v-3h-19zM12 6v14M12 6c-1.5-3.2-5.5-3.4-5.5-1.1C6.5 6.3 9.5 6 12 6zm0 0c1.5-3.2 5.5-3.4 5.5-1.1 0 1.4-3 1.1-5.5 1.1z"/></svg><span>HADIAH</span>
        </a>
        <?php endif; ?>
        <a href="#rsvp" data-nav="doa">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="1.5"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5M12 17.2s-2.6-1.6-2.6-3.2c0-.9.7-1.5 1.4-1.5.6 0 1 .3 1.2.7.2-.4.6-.7 1.2-.7.7 0 1.4.6 1.4 1.5 0 1.6-2.6 3.2-2.6 3.2z"/></svg><span>DOA &amp; UCAPAN</span>
        </a>
    </nav>
    <?php if ($musicUrl): ?>
        <audio id="bgm" src="<?= e($musicUrl) ?>" loop preload="none"></audio>
        <button type="button" class="music-btn" id="music-btn" aria-label="Putar musik latar" aria-pressed="false" <?php if ($music['title']): ?> title="<?= e($music['title']) ?><?= e($music['artist'] ? ' — '.$music['artist'] : '') ?>" <?php endif; ?>>
            <svg class="pl" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg><svg class="pa" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
        </button>
    <?php endif; ?>
    <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Galeri foto"><button class="lb-x" aria-label="Tutup">&times;</button><button class="lb-p" aria-label="Sebelumnya">&#8249;</button><img alt=""><p></p><button class="lb-n" aria-label="Berikutnya">&#8250;</button></div>
</body>
</html>
