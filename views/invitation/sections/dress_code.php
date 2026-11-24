<?php
/*
 * Section Dresscode (setelah Resepsi): kalimat pengantar, foto contoh pakaian PRIA & WANITA,
 * warna yang disarankan, catatan, dan gambar dresscode utuh (opsional).
 * Latar krem + ranting biru di sudut, sama seperti section krem lain (Introduction, The Happiest Day).
 * Di halaman publik, kolom tanpa foto & keterangan disembunyikan; di preview dashboard tampil sebagai bingkai kosong.
 */
$s = $payload['dress_code'];
$colors = array_values(array_filter($s['colors'] ?? [], fn ($c) => is_array($c) && ! empty($c['hex'])));

// Kolom pakaian yang ditampilkan (pria lalu wanita)
$people = [];
foreach (['men' => 'Pria', 'women' => 'Wanita'] as $k => $fallback) {
    $p = is_array($s[$k] ?? null) ? $s[$k] : [];
    $hasPhoto = ! empty($p['photo']['path']);
    if ($hasPhoto || filled($p['text'] ?? null) || $preview) {
        $people[] = ['label' => ($p['label'] ?? null) ?: $fallback, 'photo' => $hasPhoto ? $p['photo'] : null, 'text' => $p['text'] ?? null];
    }
}
?>
<?php if (($s['is_visible'] ?? false) || $preview): ?>
<section class="dress" id="dress_code" data-section="dress_code">
    <img class="o dr-orn tr" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>">
    <img class="o dr-orn bl" alt="" src="<?= e(asset('images/ornaments/sprig-blue-bl.png')) ?>">
    <div class="dr-card rv">
        <h2 class="dr-ttl"><?= e($s['title']) ?></h2>
        <img class="dr-div" alt="" src="<?= e(asset('images/ornaments/div-diamond-b.png')) ?>">
        <?php if (filled($s['description'] ?? null)): ?><p class="dr-lead"><?= e($s['description']) ?></p><?php endif; ?>

        <?php if ($people): ?>
            <div class="dr-pair n<?= count($people) ?>">
                <?php foreach ($people as $i => $p): ?>
                    <?php if ($i > 0): ?><span class="dr-sep" aria-hidden="true"><i></i></span><?php endif; ?>
                    <figure class="dr-col">
                        <figcaption class="dr-lb"><?= e($p['label']) ?></figcaption>
                        <?php if ($p['photo']): ?>
                            <?= inv_photo($p['photo'], ['class' => 'dr-ph'], $p['label']) ?>
                        <?php else: ?>
                            <span class="dr-ph empty" data-ph>Foto contoh pakaian <?= e(mb_strtolower($p['label'])) ?></span>
                        <?php endif; ?>
                        <?php if (filled($p['text'])): ?><p class="dr-tx"><?= e($p['text']) ?></p><?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($colors): ?>
            <div class="dr-colors">
                <?php if (filled($s['colors_title'] ?? null)): ?><h3 class="dr-sub"><span><?= e($s['colors_title']) ?></span></h3><?php endif; ?>
                <div class="dr-sw">
                    <?php foreach ($colors as $c): ?>
                        <div class="dr-swi"><i style="background:<?= e($c['hex']) ?>"></i><?php if (filled($c['name'] ?? null)): ?><span><?= e($c['name']) ?></span><?php endif; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (filled($s['note'] ?? null)): ?><p class="dr-note"><?= e($s['note']) ?></p><?php endif; ?>

        <?php if (! empty($s['image']['path'])): ?>
            <img class="dr-full" src="<?= e((new App\Services\MediaService())->url($s['image'])) ?>" alt="<?= e($s['image']['alt'] ?? $s['title']) ?>" loading="lazy" decoding="async">
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
