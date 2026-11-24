<?php /* Tombol "LIHAT LOKASI": membuka link Google Maps; bila belum ada data lokasi tampil sebagai tombol nonaktif. */ ?>
<?php $link = \App\Support\MapsLink::resolve($ev['maps_url'] ?? null, $ev['latitude'] ?? null, $ev['longitude'] ?? null, $ev['address'] ?? null); ?>
<?php if ($link): ?>
    <a class="e btn-loc <?= e($alt ?? '') ?>" style="<?= e($btnStyle) ?>" href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer"><span class="t-in"><?= e($ev['button_text']) ?></span></a>
<?php else: ?>
    <span class="e btn-loc <?= e($alt ?? '') ?>" style="<?= e($btnStyle) ?>" aria-disabled="true"><span class="t-in"><?= e($ev['button_text']) ?></span></span>
<?php endif; ?>
