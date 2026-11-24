<?php /* Section "Prepare For The Party": foto latar dan hitung mundur ke tanggal acara (data-countdown dibaca countdown.js). */ ?>
<?php $s = $payload['party']; $iso = \App\Support\EventFormat::countdownIso($payload); ?>
<section class="sec bg-navy" id="party" data-section="party" style="--dh:412;--mh:150">
    <?= inv_photo($s['background'], ['class' => 'e', 'style' => '--x:0;--y:0;--w:1440;--h:412;--op:50% 49.5%;--mx:0;--my:0;--mw:240;--mh_:150']) ?>
    <div class="tint-lite"></div>
    <h2 class="t f-script c-gold nb rv" style="--x:200;--y:56;--w:1040;--fs:81.9;--lh:2;--mx:35;--my:4;--mw:170;--mfs:19.8;--mlh:1.7"><?= e($s['title']) ?></h2>
    <p class="t f-ram c-gold rv" style="--x:200;--y:188;--w:1040;--fs:53.8;--lh:1.4;--mx:35;--my:62;--mw:170;--mfs:15.4;--mlh:1.35"><?= e($s['subtitle']) ?></p>
    <div class="cd <?= e($iso ? 'on' : '') ?>" data-countdown="<?= e($iso) ?>" role="timer" aria-label="Menuju hari pernikahan" style="left:calc(420*var(--u));top:calc(300*var(--u));width:calc(600*var(--u))">
        <div class="b"><b data-cd="d">00</b><span>Hari</span></div><div class="b"><b data-cd="h">00</b><span>Jam</span></div><div class="b"><b data-cd="m">00</b><span>Menit</span></div><div class="b"><b data-cd="s">00</b><span>Detik</span></div>
    </div>
</section>
