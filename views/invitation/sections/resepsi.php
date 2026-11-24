<?php /* Section Resepsi: tanggal, jam, lokasi, peta, tombol Google Maps, foto utama (bingkai lengkung) dan foto pendamping. */ ?>
<?php $ev = $payload['resepsi'];
    $fallback = $payload['cover']['date_fallback'];
    $dateText = \App\Support\EventFormat::date($ev) ?? $fallback;
    $timeText = \App\Support\EventFormat::time($ev);
    $venue = \App\Support\Inv::text($ev['venue_name'], $preview);
    $addr = \App\Support\Inv::text($ev['address'], $preview);
    $mapStyle = '--x:212;--y:775;--w:452;--h:264;--mx:36;--my:500;--mw:177;--mh_:103';
    $btnStyle = '--x:815;--y:955;--w:309;--h:84;--mx:75;--my:622;--mw:96;--mh_:26;--fs:23.1;--mfs:7.2'; ?>
<section class="sec sec-resepsi" id="resepsi" data-section="resepsi" style="--dh:1125;--mh:675">
    <div class="e paper" style="--x:102;--y:46;--w:1236;--h:1038;--mx:24;--my:24;--mw:192;--mh_:639"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>" style="--x:1108;--y:44;--w:230;--h:147;--mx:150;--my:24;--mw:66;--mh_:42">
    <img class="e o d" alt="" src="<?= e(asset('images/ornaments/sprig-blue-bl.png')) ?>" style="--x:100;--y:960;--w:240;--h:154">
    <div class="e" style="--x:700;--y:108;--w:612;--h:649;--mx:34;--my:47;--mw:172;--mh_:183;background:#f2e6d8"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/rings-divider.png')) ?>" style="--x:779;--y:148;--w:453;--h:57;--mx:51;--my:66;--mw:127;--mh_:16">
    <div class="e dcard" style="--x:808;--y:204;--w:424;--h:289;--mx:60;--my:80;--mw:119;--mh_:81"></div>
    <p class="t f-script c-navy nb ls2" style="--x:808;--y:252;--w:424;--fs:59.7;--lh:2;--mx:60;--my:93;--mw:119;--mfs:16.8"><?= e($ev['title']) ?></p>
    <p class="t f-cardo c-navy nb" style="--x:808;--y:372;--w:424;--fs:27.5;--lh:1.35;--mx:60;--my:127;--mw:119;--mfs:7.7"><?= e($dateText) ?></p>
    <?php if ($timeText): ?><p class="t f-cardo c-navy nb" style="--x:808;--y:418;--w:424;--fs:27.5;--lh:1.35;--mx:60;--my:140;--mw:119;--mfs:7.7"><?= e($timeText) ?></p><?php endif; ?>
    <?= inv_photo($ev['photo_side'], ['class' => 'e', 'style' => '--x:815;--y:519;--w:394;--h:178;--mx:63;--my:170;--mw:111;--mh_:51']) ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/arch-frame.png')) ?>" style="--x:199;--y:138;--w:451;--h:577;--mx:59;--my:238;--mw:122;--mh_:157">
    <?= inv_photo($ev['photo_main'], ['class' => 'e', 'style' => '--x:228;--y:177;--w:392;--h:503;--mx:67;--my:249;--mw:106;--mh_:136;border-radius:calc(196*var(--u)) calc(196*var(--u)) 0 0']) ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/seal-small.png')) ?>" style="--x:394;--y:109;--w:90;--h:88;--mx:112;--my:230;--mw:24;--mh_:24">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/seal-ribbon.png')) ?>" style="--x:364;--y:611;--w:136;--h:163;--mx:104;--my:367;--mw:36;--mh_:44">
    <p class="t f-cardob c-navym nb" style="--x:781;--y:764;--w:412;--fs:27.5;--lh:1.35;--mx:50;--my:418;--mw:140;--mfs:8.5"><?= e($venue) ?></p>
    <p class="t f-cardo c-navym" style="--x:793;--y:820;--w:395;--fs:26;--lh:1.41;--mx:58;--my:436;--mw:124;--mfs:8.1;--mlh:1.4"><?= e($addr) ?></p>
    <?= view('invitation.partials.map', ['ev' => $ev, 'mapStyle' => $mapStyle, 'preview' => $preview, 'nfs' => 16]) ?>
    <?= view('invitation.partials.locbtn', ['ev' => $ev, 'btnStyle' => $btnStyle, 'alt' => 'alt']) ?>
</section>
