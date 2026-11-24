<?php /* Section Bride & Groom: banner, mempelai wanita (bagian atas) dan mempelai pria (bagian bawah) beserta nama orang tua. */ ?>
<?php $s = $payload['couple']; $b = $s['bride']; $g = $s['groom']; ?>
<section class="sec bg-navy" id="couple" data-section="couple" style="--dh:900;--mh:525">
    <?= inv_photo($s['background'], ['class' => 'e', 'style' => '--x:0;--y:0;--w:1440;--h:333;--op:50% 100%;--mx:0;--my:0;--mw:240;--mh_:70']) ?>
    <div class="e" style="--x:0;--y:0;--w:1440;--h:333;--mx:0;--my:0;--mw:240;--mh_:70;background:rgba(46,72,109,.6)"></div>
    <h2 class="t f-script c-gold nb rv" style="--x:300;--y:40;--w:840;--fs:97.3;--lh:2;--mx:10;--my:72;--mw:220;--mfs:24.1"><?= e($s['title']) ?></h2>
    <p class="t f-cgm c-gold rv" style="--x:380;--y:236;--w:680;--fs:35.3;--lh:1.22;--mx:50;--my:120;--mw:140;--mfs:11.8;--mlh:1.25"><?= e($s['subtitle']) ?></p>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-gold-dotted.png')) ?>" style="--x:316;--y:334;--w:805;--h:18;--mx:15;--my:178;--mw:203;--mh_:5">
    <img class="e o d" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-wide.png')) ?>" style="--x:0;--y:363;--w:169;--h:498">
    <img class="e o m" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-tall.png')) ?>" style="--mx:0;--my:289;--mw:43;--mh_:127">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/lace-frame-a.png')) ?>" style="--x:248;--y:369;--w:377;--h:483;--mx:54;--my:198;--mw:127;--mh_:162">
    <?= inv_photo($b['photo'], ['class' => 'e', 'style' => '--x:314;--y:439;--w:251;--h:341;--mx:77;--my:221;--mw:84;--mh_:116']) ?>
    <p class="t f-allura c-ivory nb" style="--x:654;--y:438;--w:400;--fs:119.1;--lh:1.25;--mx:60;--my:366;--mw:120;--mfs:28.9;--mlh:1.25"><?= e($b['nickname']) ?></p>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-diamond-b.png')) ?>" style="--x:692;--y:610;--w:328;--h:28;--mx:79;--my:410;--mw:79;--mh_:7">
    <?php $bp = \App\Support\Inv::parents($b, $preview); ?>
    <?php if ($bp !== ''): ?>
    <p class="t f-cgb c-ivory ls3" style="--x:560;--y:686;--w:600;--fs:29.7;--lh:1.2;--mx:50;--my:430;--mw:140;--mfs:9.2;--mlh:1.2"><span style="display:block"><?= e($b['child_label']) ?></span><span style="display:block"><?= e($bp) ?></span></p>
    <?php endif; ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/bouquet-line.png')) ?>" style="--x:1028;--y:424;--w:296;--h:346;--mx:98;--my:464;--mw:40;--mh_:46">
</section>
<section class="sec bg-navy" id="groom" data-section="couple" style="--dh:675;--mh:300">
    <?= inv_photo($s['side_photo'], ['class' => 'e d', 'style' => '--x:0;--y:0;--w:420;--h:675;--op:30% 50%']) ?>
    <?= inv_photo($s['side_photo'], ['class' => 'e m', 'style' => '--mx:0;--my:248;--mw:240;--mh_:52;--op:50% 100%']) ?>
    <div class="e d" style="--x:0;--y:0;--w:420;--h:675;background:rgba(46,72,109,.4)"></div><div class="e m" style="--mx:0;--my:248;--mw:240;--mh_:52;background:rgba(46,72,109,.4)"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/birds-pair.png')) ?>" style="--x:178;--y:16;--w:344;--h:358;--mx:10;--my:186;--mw:44;--mh_:60;opacity:.9">
    <p class="t f-allura c-ivory nb" style="--x:410;--y:167;--w:300;--fs:107.1;--lh:1.25;--mx:70;--my:172;--mw:110;--mfs:26.5;--mlh:1.3"><?= e($g['nickname']) ?></p>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-diamond-b.png')) ?>" style="--x:351;--y:319;--w:360;--h:25;--mx:79;--my:211;--mw:89;--mh_:6">
    <?php $gp = \App\Support\Inv::parents($g, $preview); ?>
    <?php if ($gp !== ''): ?>
    <p class="t f-cgb c-ivory ls3" style="--x:290;--y:380;--w:500;--fs:30.6;--lh:1.22;--mx:55;--my:224;--mw:130;--mfs:7.6;--mlh:1.3"><span style="display:block"><?= e($g['child_label']) ?></span><span style="display:block"><?= e($gp) ?></span></p>
    <?php endif; ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/lace-frame-b.png')) ?>" style="--x:802;--y:98;--w:377;--h:482;--mx:54;--my:9;--mw:133;--mh_:170">
    <?= inv_photo($g['photo'], ['class' => 'e', 'style' => '--x:873;--y:157;--w:242;--h:357;--mx:84;--my:33;--mw:77;--mh_:113']) ?>
    <img class="e o d" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-wide.png')) ?>" style="--x:1293;--y:98;--w:147;--h:420;transform:scaleX(-1)">
</section>
