<?php /* Cover undangan: nama mempelai, tanggal, nama tamu personal, dan tombol BUKA UNDANGAN (gerbang terkunci di halaman publik). */ ?>
<?php $c = $payload['cover'];
    $date = \App\Support\EventFormat::coverDate($payload);
    $gname = $guest['name'] ?? $c['guest_fallback']; // nama tamu dari link personal ?>
<div class="cover-wrap <?= e($preview ? '' : 'gate') ?>" id="cover" data-section="cover">
    <?= inv_photo($c['background'], ['class' => 'cv-bgph'], eager: true) ?>
    <div class="cv-tint"></div>
    <div class="sec" style="--dh:810;--mh:480">
        <div class="e cv-card" style="--x:257;--y:34;--w:926;--h:742;--mx:24;--my:52;--mw:192;--mh_:370"></div>
        <img class="e o d" alt="" src="<?= e(asset('images/ornaments/div-line-a.png')) ?>" style="--x:446;--y:118;--w:240;--h:17">
        <img class="e o d" alt="" src="<?= e(asset('images/ornaments/div-line-a.png')) ?>" style="--x:771;--y:118;--w:241;--h:17">
        <p class="t f-fr c-navy ls nbd" style="--x:520;--y:70;--w:400;--fs:27.1;--lh:1.3;--mx:50;--my:64;--mw:140;--mfs:13.7;--mlh:1.3"><?= e($c['eyebrow']) ?></p>
        <p class="t f-script c-navy nb" style="--x:512;--y:136;--w:440;--fs:77.5;--lh:2;--mx:60;--my:98;--mw:120;--mfs:28.2"><?= e($c['bride_name']) ?></p>
        <p class="t f-script c-navy nb" style="--x:612;--y:244;--w:240;--fs:77.5;--lh:2;--mx:90;--my:137;--mw:60;--mfs:28.2">&amp;</p>
        <p class="t f-script c-navy nb" style="--x:512;--y:352;--w:440;--fs:77.5;--lh:2;--mx:60;--my:176;--mw:120;--mfs:28.2"><?= e($c['groom_name']) ?></p>
        <p class="t f-alice c-navy ls2 nbd" style="--x:495;--y:492;--w:500;--fs:26.9;--lh:1.2;--mx:40;--my:224;--mw:160;--mfs:11.4;--mlh:1.4">
            <?= e($c['show_date'] ? mb_strtoupper($date ?? $c['date_fallback']) : '') ?></p>
        <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-diamond.png')) ?>" style="--x:600;--y:540;--w:309;--h:34;--mx:100;--my:260;--mw:52;--mh_:10">
        <p class="t f-fri c-navy" style="--x:470;--y:566;--w:550;--fs:30;--lh:1.2;--mx:45;--my:270;--mw:150;--mfs:17.2;--mlh:1.4"><span><?= e($c['guest_label']) ?></span> <span class="cv-guest-name <?= e(mb_strlen($gname) > 24 ? 'long' : '') ?>" data-guest-name><?= e($gname) ?></span></p>
        <button type="button" class="e cv-btn" id="open-invitation" style="--x:484;--y:641;--w:505;--h:109;--fs:28.1;--mx:50;--my:340;--mw:144;--mh_:31;--mfs:12.5">
            <img class="ico d" alt="" src="<?= e(asset('images/ornaments/icon-envelope.png')) ?>"><span><?= e($c['button_text']) ?></span>
        </button>
    </div>
</div>
