<?php /* Section Akad Nikah (Save the Date): tanggal, jam, lokasi, peta, tombol Google Maps, dan kolase 4 foto. Koordinat --x/--y = desktop, --mx/--my = mobile. */ ?>
<?php $ev = $payload['akad'];
    $fallback = $payload['cover']['date_fallback'];
    $dateText = \App\Support\EventFormat::date($ev) ?? $fallback;
    $timeText = \App\Support\EventFormat::time($ev);
    $venue = \App\Support\Inv::text($ev['venue_name'], $preview);
    $addr = \App\Support\Inv::text($ev['address'], $preview);
    $mapStyle = '--x:791;--y:825;--w:453;--h:264;--mx:21;--my:635;--mw:198;--mh_:115';
    $btnStyle = '--x:238;--y:985;--w:292;--h:85;--mx:49;--my:771;--mw:138;--mh_:40;--fs:20.5;--mfs:9.7';
    $ph = fn($i) => $ev['photos'][$i] ?? null; ?>
<section class="sec sec-akad" id="akad" data-section="akad" style="--dh:1125;--mh:825">
    <div class="ticket"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-plane-dotted.png')) ?>" style="--x:86;--y:118;--w:618;--h:51;--mx:15;--my:48;--mw:211;--mh_:18">
    <h2 class="t f-allura c-ivory nb rv" style="--x:115;--y:46;--w:516;--fs:83.2;--lh:1.25;--mx:32;--my:22;--mw:176;--mfs:28.3"><?= e($ev['heading']) ?></h2>
    <p class="t f-cardoi c-ivory rv" style="--x:132;--y:177;--w:488;--fs:29.5;--lh:1.47;--mx:38;--my:65;--mw:160;--mfs:10;--mlh:1.45"><?= e($ev['intro']) ?></p>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/rings-gold.png')) ?>" style="--x:70;--y:308;--w:202;--h:164;--mx:14;--my:109;--mw:69;--mh_:56">
    <div class="e sheet" style="--x:178;--y:365;--w:388;--h:267;--mx:51;--my:128;--mw:132;--mh_:91"></div>
    <p class="t f-pf c-navy nb" style="--x:177;--y:400;--w:400;--fs:24;--lh:1.33;--mx:62;--my:141;--mw:112;--mfs:8.2"><?= e($dateText) ?></p>
    <?php if ($timeText): ?><p class="t f-pf c-navy nb" style="--x:177;--y:441;--w:400;--fs:24;--lh:1.33;--mx:62;--my:154;--mw:112;--mfs:8.2"><?= e($timeText) ?></p><?php endif; ?>
    <p class="t f-allura c-navy nb" style="--x:222;--y:493;--w:300;--fs:75.3;--lh:1.2;--mx:60;--my:172;--mw:112;--mfs:25.6"><?= e($ev['title']) ?></p>
    <p class="t f-cardoi c-ivory" style="--x:206;--y:647;--w:340;--fs:33.3;--lh:1.35;--mx:50;--my:224;--mw:140;--mfs:11.4;--mlh:1.4"><?= e($ev['closing']) ?></p>

    <div class="e panel-cream" style="--x:700;--y:109;--w:587;--h:667;--mx:9;--my:276;--mw:216;--mh_:246"></div>
    <?= inv_photo($ph(0), ['class' => 'e', 'style' => '--x:731;--y:169;--w:243;--h:425;--mx:20;--my:298;--mw:90;--mh_:158']) ?>
    <?= inv_photo($ph(1), ['class' => 'e', 'style' => '--x:1018;--y:170;--w:240;--h:152;--mx:126;--my:298;--mw:89;--mh_:57']) ?>
    <?= inv_photo($ph(2), ['class' => 'e', 'style' => '--x:1018;--y:345;--w:241;--h:390;--mx:126;--my:363;--mw:89;--mh_:144']) ?>
    <?= inv_photo($ph(3), ['class' => 'e', 'style' => '--x:731;--y:603;--w:243;--h:131;--mx:25;--my:464;--mw:87;--mh_:49']) ?>

    <img class="e o" alt="" src="<?= e(asset('images/ornaments/orchid.png')) ?>" style="--x:16;--y:844;--w:177;--h:196;--mx:24;--my:546;--mw:61;--mh_:67">
    <p class="t f-pfb c-ivory nb" style="--x:195;--y:839;--w:360;--fs:24;--lh:1.35;--mx:74;--my:539;--mw:150;--mfs:8.2"><?= e($venue) ?></p>
    <p class="t f-pf c-ivory" style="--x:204;--y:895;--w:360;--fs:19.9;--lh:1.36;--mx:74;--my:562;--mw:150;--mfs:6.8;--mlh:1.35"><?= e($addr) ?></p>
    <?= view('invitation.partials.map', ['ev' => $ev, 'mapStyle' => $mapStyle, 'preview' => $preview, 'nfs' => 16]) ?>
    <?= view('invitation.partials.locbtn', ['ev' => $ev, 'btnStyle' => $btnStyle]) ?>
</section>
