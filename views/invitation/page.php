<?php
/*
 * Seluruh isi undangan. Dipakai landing publik DAN Live Preview dashboard (template & CSS yang sama),
 * sehingga tampilan preview identik dengan yang dilihat tamu.
 * Variabel: $payload (konten), $guest (tamu atau null), $wishes (ucapan), $preview (true di dashboard).
 */
$preview = $preview ?? false;
$guest = $guest ?? null;

// Urutan section: Dresscode setelah Resepsi, Kirim Hadiah setelah Doa & Ucapan (rsvp).
// Variabel yang diteruskan ke setiap file section
$__vars = ['payload' => $payload, 'guest' => $guest, 'wishes' => $wishes ?? [], 'preview' => $preview];
?>
<?= view('invitation.sections.cover', $__vars) ?>
<main id="invitation-body" class="<?= e(($preview ?? false) ? 'is-preview' : '') ?>">
    <?php foreach (['intro', 'quote', 'couple_intro', 'couple', 'happiest_day', 'akad', 'party', 'resepsi', 'dress_code', 'gallery', 'story_intro', 'love_story', 'rsvp', 'gift', 'closing'] as $__sec): ?>
        <?= view('invitation.sections.'.$__sec, $__vars) ?>
    <?php endforeach; ?>
</main>
