<?php /* Section "Yang Berbahagia": judul dan kalimat pengantar mempelai. */ ?>
<?php $s = $payload['couple_intro']; ?>
<section class="sec bg-navy" id="couple_intro" data-section="couple_intro" style="--dh:412;--mh:150">
    <?= inv_photo($s['photo'], ['class' => 'e', 'style' => '--x:523;--y:187;--w:394;--h:178;--mx:71;--my:109;--mw:92;--mh_:41;opacity:.45']) ?>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-gold-tr.png')) ?>" style="--x:1141;--y:22;--w:257;--h:164;--mx:178;--my:0;--mw:62;--mh_:39">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/sprig-gold-bl.png')) ?>" style="--x:42;--y:226;--w:256;--h:164;--mx:0;--my:114;--mw:62;--mh_:36">
    <h2 class="t f-script c-ivory rv" style="--x:300;--y:56;--w:840;--fs:81.9;--lh:2;--mx:55;--my:8;--mw:130;--mfs:19.8;--mlh:1.7"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-ivory rv" style="--x:200;--y:187;--w:1040;--fs:53.8;--lh:1.4;--mx:55;--my:66;--mw:130;--mfs:13.1;--mlh:1.4"><?= e($s['text']) ?></p>
</section>
