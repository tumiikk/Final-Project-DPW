<?php /* Section kutipan ayat (QS. Ar-Rum: 21) di atas kartu, dengan foto latar. */ ?>
<?php $s = $payload['quote']; ?>
<section class="sec bg-navy" id="quote" data-section="quote" style="--dh:810;--mh:480">
    <?= inv_photo($s['background'], ['class' => 'e', 'style' => '--x:0;--y:0;--w:1440;--h:810;--mx:0;--my:0;--mw:240;--mh_:480']) ?>
    <div class="tint-navy" style="background:rgba(46,72,109,.7)"></div>
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/div-fancy-gold.png')) ?>" style="--x:451;--y:56;--w:549;--h:50;--mx:52;--my:32;--mw:136;--mh_:13">
    <div class="e" style="--x:260;--y:155;--w:921;--h:513;--mx:38;--my:64;--mw:164;--mh_:355;background:url('<?= e(asset('images/ornaments/quote-card.png')) ?>') center/100% 100% no-repeat"></div>
    <img class="e o d" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-tall.png')) ?>" style="--x:1182;--y:227;--w:154;--h:456">
    <img class="e o d flip" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-tall.png')) ?>" style="--x:104;--y:227;--w:154;--h:456">
    <img class="e o m" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-tall.png')) ?>" style="--mx:202;--my:283;--mw:42;--mh_:123">
    <img class="e o m flip" alt="" src="<?= e(asset('images/ornaments/flower-watercolor-tall.png')) ?>" style="--mx:-4;--my:91;--mw:41;--mh_:123">
    <img class="e o" alt="" src="<?= e(asset('images/ornaments/swans-sketch.png')) ?>" style="--x:407;--y:445;--w:569;--h:329;--mx:64;--my:363;--mw:112;--mh_:64;opacity:.55">
    <p class="t f-pf c-ink rv wrap" style="--x:283;--y:224;--w:875;--fs:33;--lh:1.66;--mx:46;--my:90;--mw:148;--mfs:11.1;--mlh:1.67"><?= e($s['text']) ?></p>
    <p class="t f-pf c-ink" style="--x:480;--y:567;--w:480;--fs:31.7;--lh:1.3;--mx:40;--my:333;--mw:160;--mfs:14.5">— (<?= e($s['source']) ?>) —</p>
    <?php if (! empty($s['note'])): ?><p class="t f-pf c-ink" style="--x:480;--y:620;--w:480;--fs:22;--mx:40;--my:362;--mw:160;--mfs:8"><?= e($s['note']) ?></p><?php endif; ?>
</section>
