<?php /* Section "The Happiest Day": judul, subjudul, dan dua foto. */ ?>
<?php $s = $payload['happiest_day']; ?>
<section class="sec bg-cream" id="happiest_day" data-section="happiest_day" style="--dh:412;--mh:150">
    <?= inv_photo($s['photo_top'], ['class' => 'e', 'style' => '--x:41;--y:41;--w:409;--h:159;--mx:9;--my:12;--mw:89;--mh_:34;opacity:.85']) ?>
    <?= inv_photo($s['photo_bottom'], ['class' => 'e fade-gray', 'style' => '--x:1005;--y:206;--w:394;--h:178;--mx:124;--my:106;--mw:98;--mh_:44']) ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>" style="--x:1141;--y:22;--w:257;--h:164;--mx:187;--my:0;--mw:53;--mh_:37">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-bl.png')) ?>" style="--x:42;--y:226;--w:256;--h:164;--mx:5;--my:114;--mw:52;--mh_:33">
    <h2 class="t f-script c-navym nb rv" style="--x:300;--y:55;--w:840;--fs:81.9;--lh:2;--mx:20;--my:48;--mw:200;--mfs:20.5"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-navym rv" style="--x:400;--y:238;--w:700;--fs:53.8;--lh:1.4;--mx:40;--my:80;--mw:160;--mfs:12.5"><?= e($s['subtitle']) ?></p>
</section>
