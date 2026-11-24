<?php /* Kotak peta tertanam (OpenStreetMap) bila latitude & longitude diisi; di preview tampil petunjuk bila kosong. */ ?>
<?php $lat = $ev['latitude'] ?? null; $lng = $ev['longitude'] ?? null;
    $hasCoord = is_numeric($lat) && is_numeric($lng);
    $link = \App\Support\MapsLink::resolve($ev['maps_url'] ?? null, $lat, $lng, $ev['address'] ?? null); ?>
<div class="e mapbox" style="<?= e($mapStyle) ?>">
    <?php if ($hasCoord): ?>
        <iframe title="Peta lokasi" loading="lazy" referrerpolicy="no-referrer"
            src="https://www.openstreetmap.org/export/embed.html?bbox=<?= e(round($lng - 0.004, 6)) ?>,<?= e(round($lat - 0.003, 6)) ?>,<?= e(round($lng + 0.004, 6)) ?>,<?= e(round($lat + 0.003, 6)) ?>&amp;layer=mapnik&amp;marker=<?= e($lat) ?>,<?= e($lng) ?>"></iframe>
    <?php elseif ($preview): ?>
        <div class="nomap" style="--nfs: <?= e($nfs ?? 16) ?>">Peta tampil setelah koordinat lokasi diisi</div>
    <?php endif; ?>
</div>
