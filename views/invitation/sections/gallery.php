<?php /* Section Our Moments (galeri): kolase 9 foto sesuai mockup + baris tambahan + data lightbox. */ ?>
<?php $s = $payload['gallery'];
    // Hanya foto yang aktif, urutan sesuai dashboard
    $items = array_values(array_filter($s['items'] ?? [], fn ($i) => ($i['is_active'] ?? true)));
    // slot: [dx,dy,dw,dh, mx,my,mw,mh]
    $slots = [
        [134,262,490,342, 45,107,159,112],
        [118,654,522,138, 40,235,170,45],
        [138,916,472,403, 47,320,153,131],
        [700,163,436,290, 47,484,111,73],
        [1179,170,126,316, 168,486,31,80],
        [699,474,181,307, 47,562,46,77],
        [908,511,415,269, 100,572,105,67],
        [665,862,255,521, 39,660,64,131],
        [998,959,328,378, 123,684,82,96],
    ];
    $slotStyle = fn ($n) => "--x:{$n[0]};--y:{$n[1]};--w:{$n[2]};--h:{$n[3]};--mx:{$n[4]};--my:{$n[5]};--mw:{$n[6]};--mh_:{$n[7]}";
    $n = count($items);
    $slotted = array_slice($items, 0, 9);          // 9 foto pertama mengisi kolase mockup
    $extra = array_values(array_slice($items, 9)); // sisanya tampil sebagai baris tambahan ?>
<section class="sec bg-navy" id="gallery" data-section="gallery" style="--dh:1500;--mh:825">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/gallery-title-divider.png')) ?>" style="--x:109;--y:175;--w:497;--h:34;--mx:24;--my:62;--mw:193;--mh_:12">
    <h2 class="t f-script c-ivory nb rv" style="--x:100;--y:51;--w:560;--fs:76.9;--lh:2;--mx:20;--my:14;--mw:210;--mfs:29.8"><?= e($s['title']) ?></h2>
    <p class="t f-cg c-ivory l" style="--x:110;--y:201;--w:520;--fs:25.2;--lh:1.25;--mx:24;--my:72;--mw:195;--mfs:9.8;text-align:left;white-space:nowrap"><?= e($s['description']) ?></p>

    <?php if ($n >= 1): ?><div class="e" style="<?= e($slotStyle([117,250,523,380, 40,104,170,123])) ?>;background:url('<?= e(asset('images/ornaments/gallery-card-a.png')) ?>') center/100% 100% no-repeat"></div><?php endif; ?>
    <?php if ($n >= 3): ?><div class="e" style="<?= e($slotStyle([108,820,532,537, 37,288,172,175])) ?>;background:url('<?= e(asset('images/ornaments/gallery-card-b.png')) ?>') center/100% 100% no-repeat"></div><?php endif; ?>
    <?php if ($n >= 4): ?><div class="e" style="<?= e($slotStyle([680,143,669,776, 43,479,168,195])) ?>;background:#f2e6d8"></div><?php endif; ?>
    <?php if ($n >= 9): ?><div class="e" style="<?= e($slotStyle([974,946,376,439, 116,682,126,109])) ?>;background:url('<?= e(asset('images/ornaments/gallery-card-b.png')) ?>') center/100% 100% no-repeat"></div><?php endif; ?>

    <?php foreach ($slotted as $i => $it): ?>
        <button type="button" class="e gal-item ph-btn" data-gal="<?= e($i) ?>" aria-label="Perbesar foto <?= e($i + 1) ?>" style="<?= e($slotStyle($slots[$i])) ?>;padding:0;border:0;background:none;position:absolute">
            <?= inv_photo($it['photo'], ['style' => 'width:100%;height:100%']) ?>
            <?php if (! empty($it['caption']) && $i !== 2): ?><span class="cap" style="--cfs:<?= e(20) ?>"><?= e($it['caption']) ?></span><?php endif; ?>
        </button>
    <?php endforeach; ?>
    <?php if ($n >= 3 && ! empty($slotted[2]['caption'])): ?>
        <p class="t f-ram c-navy nb" style="--x:108;--y:828;--w:532;--fs:49.4;--lh:1.6;--mx:37;--my:292;--mw:172;--mfs:16"><?= e($slotted[2]['caption']) ?></p>
    <?php endif; ?>
    <?php if ($n >= 4 && ! empty($s['group_caption'])): ?>
        <p class="t f-ram c-navy" style="--x:835;--y:797;--w:360;--fs:36.8;--lh:1.38;--mx:72;--my:643;--mw:110;--mfs:9.3;--mlh:1.4"><?= e($s['group_caption']) ?></p>
    <?php endif; ?>
</section>
<?php if ($extra): ?>
    <div class="gal-extra">
        <?php foreach ($extra as $j => $it): ?>
            <button type="button" class="gx" data-gal="<?= e(9 + $j) ?>" aria-label="Perbesar foto <?= e(10 + $j) ?>" style="padding:0"><?= inv_photo($it['photo'], ['style' => 'width:100%;height:100%;border:0']) ?><?php if (! empty($it['caption'])): ?><span class="cap" style="--cfs:1"><?= e($it['caption']) ?></span><?php endif; ?></button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($items): ?>
    <script type="application/json" id="gallery-data"><?= json_encode(array_map(fn ($it) => ['src' => (new \App\Services\MediaService())->url($it['photo']), 'caption' => $it['caption'] ?? ''], $items), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<?php endif; ?>
