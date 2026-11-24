<?php /* Section Introduction "A little note of love": judul, kalimat, dua foto. */ ?>
<?php $s = $payload['intro']; ?>
<section class="sec bg-cream" id="intro" data-section="intro" style="--dh:412;--mh:150">
    <?= inv_photo($s['photo_top'], ['class' => 'e', 'style' => '--x:40;--y:46;--w:324;--h:117;--op:50% 36%;--mx:5;--my:4;--mw:107;--mh_:33']) ?>
    <?= inv_photo($s['photo_bottom'], ['class' => 'e', 'style' => '--x:918;--y:257;--w:445;--h:133;--mx:117;--my:111;--mw:108;--mh_:33']) ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>" style="--x:1141;--y:22;--w:257;--h:164;--mx:187;--my:3;--mw:53;--mh_:34">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-bl.png')) ?>" style="--x:42;--y:226;--w:256;--h:164;--mx:5;--my:114;--mw:52;--mh_:33">
    <h2 class="t f-script c-navym nb rv" style="--x:372;--y:56;--w:760;--fs:81.9;--lh:2;--mx:20;--my:42;--mw:200;--mfs:20.5"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-navym rv" style="--x:200;--y:188;--w:1040;--fs:53.8;--lh:1.4;--mx:35;--my:74;--mw:170;--mfs:12.5;--mlh:1.45"><?= e($s['text']) ?></p>
</section>
