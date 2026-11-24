<?php /* Section "Let See How This Story Begins": judul, subjudul, dan foto oval. */ ?>
<?php $s = $payload['story_intro']; ?>
<section class="sec bg-cream" id="story_intro" data-section="story_intro" style="--dh:412;--mh:150">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>" style="--x:1141;--y:22;--w:257;--h:164;--mx:187;--my:0;--mw:53;--mh_:37">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-blue-bl.png')) ?>" style="--x:16;--y:220;--w:256;--h:164;--mx:5;--my:114;--mw:52;--mh_:33">
    <?= inv_photo($s['photo'], ['class' => 'e ovalph', 'style' => '--x:1196;--y:176;--w:208;--h:236;--mx:150;--my:79;--mw:80;--mh_:71']) ?>
    <h2 class="t f-script c-navym rv" style="--x:100;--y:97;--w:1100;--fs:68.2;--lh:1.7;--mx:25;--my:22;--mw:190;--mfs:19.2;--mlh:1.55"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-navym rv" style="--x:200;--y:226;--w:1000;--fs:53.8;--lh:1.3;--mx:50;--my:86;--mw:120;--mfs:10.7;--mlh:1.4"><?= e($s['subtitle']) ?></p>
</section>
