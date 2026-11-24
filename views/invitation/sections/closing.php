<?php /* Section penutup "Mohon Doa Restu": teks terima kasih, foto oval, dan foto latar bawah. */ ?>
<?php $s = $payload['closing']; ?>
<section class="sec sec-closing" id="closing" data-section="closing" style="--dh:600;--mh:300">
    <?= inv_photo($s['background'], ['class' => 'e', 'style' => '--x:0;--y:203;--w:1440;--h:397;--mx:0;--my:135;--mw:240;--mh_:165']) ?>
    <div class="e" style="--x:0;--y:203;--w:1440;--h:397;--mx:0;--my:135;--mw:240;--mh_:165;background:rgba(46,72,109,.55)"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-gold-tr.png')) ?>" style="--x:1208;--y:9;--w:214;--h:137;--mx:183;--my:0;--mw:57;--mh_:36">
    <img class="e o flip" alt="" src="<?= e(asset('images/ornaments/sprig-gold-tr.png')) ?>" style="--x:60;--y:34;--w:214;--h:137;--mx:0;--my:88;--mw:64;--mh_:41">
    <h2 class="t f-script c-ivory nb rv" style="--x:300;--y:32;--w:840;--fs:68.3;--lh:2;--mx:20;--my:23;--mw:200;--mfs:19.3"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-ivory rv nbd" style="--x:200;--y:142;--w:1040;--fs:41.8;--lh:1.4;--mx:50;--my:57;--mw:140;--mfs:12.5;--mlh:1.4"><?= e($s['text']) ?></p>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/oval-frame.png')) ?>" style="--x:586;--y:200;--w:268;--h:380;--mx:65;--my:135;--mw:111;--mh_:156">
    <?= inv_photo($s['photo'], ['class' => 'e ovalph', 'style' => '--x:617;--y:255;--w:206;--h:272;--mx:78;--my:157;--mw:85;--mh_:112']) ?>
</section>
