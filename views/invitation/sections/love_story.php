<?php /* Section Our Love Story: judul, deskripsi, dan timeline cerita; setiap cerita punya foto sendiri (0, 1, 2-3, atau 4+ foto). */ ?>
<?php $s = $payload['love_story'];
    $items = array_values($s['items'] ?? []);
    $descParts = preg_split('/(pasangan hidup\.?)/iu', (string) ($s['description'] ?? ''), 2, PREG_SPLIT_DELIM_CAPTURE);
    // posisi foto (persen dalam panel): [l,t,w,h]
    $layouts = [
        2 => [[1.5,3.7,51.5,70.3],[46.5,20.6,53.3,75.4]],
        3 => [[19.1,2.4,55.2,49.7],[2.6,49.8,58.5,47.4],[73.2,58.5,24.8,34.7]],
    ]; ?>
<section class="story" id="love_story" data-section="love_story">
    <div class="sec bg-navy" style="--dh:290;--mh:112">
        <img class="e o d" alt="" src="<?= e(asset('images/ornaments/div-dash-l.png')) ?>" style="--x:120;--y:50;--w:566;--h:18">
        <img class="e o d" alt="" src="<?= e(asset('images/ornaments/story-plane.png')) ?>" style="--x:686;--y:34;--w:51;--h:51">
        <img class="e o d" alt="" src="<?= e(asset('images/ornaments/div-dash.png')) ?>" style="--x:754;--y:50;--w:549;--h:18">
        <img class="e o m" alt="" src="<?= e(asset('images/ornaments/div-dash.png')) ?>" style="--mx:15;--my:24;--mw:210;--mh_:7">
        <h2 class="t f-script c-ivory l nb rv" style="--x:120;--y:84;--w:640;--fs:74.6;--lh:2;--mx:24;--my:36;--mw:210;--mfs:27.6;text-align:left"><?= e($s['title']) ?></h2>
        <p class="t f-pf c-ivory l rv" style="--x:147;--y:223;--w:430;--fs:22;--lh:1.35;--mx:34;--my:88;--mw:160;--mfs:8.1;--mlh:1.4;text-align:left;text-wrap:wrap"><?= e($descParts[0] ?? '') ?><?php if (isset($descParts[1])): ?><b style="font-style:italic"><?= e($descParts[1]) ?></b><?= e($descParts[2] ?? '') ?><?php endif; ?></p>
    </div>
    <div class="rail">
        <?php foreach ($items as $index => $it): ?>
            <?php $photos = array_values($it['photos'] ?? []); $cnt = count($photos); ?>
            <article class="item rv" data-story="<?= e($it['id'] ?? $index) ?>">
                <div class="txt">
                    <span class="yr"><?= e($it['year']) ?></span><span class="tt"><?= e($it['title']) ?></span>
                    <?php if (! empty($it['description'])): ?><p class="ds"><?= e($it['description']) ?></p><?php endif; ?>
                </div>
                <div class="col">
                    <?php if ($cnt === 0): ?>
                        <div class="collage empty" style="aspect-ratio:649/200"><div class="rings"><img alt="" src="<?= e(asset('images/ornaments/rings-gold.png')) ?>" style="opacity:.85"></div></div>
                    <?php elseif ($cnt === 1): ?>
                        <div class="collage n1"><?= inv_photo($photos[0], ['style' => '--l:12;--t:8;--cw:76;--ch:84']) ?></div>
                    <?php elseif ($cnt <= 3): ?>
                        <div class="collage <?= e($cnt === 3 ? 'n3' : '') ?>">
                            <?php foreach ($photos as $pi => $ph): ?><?= inv_photo($ph, ['style' => '--l:'.$layouts[$cnt][$pi][0].';--t:'.$layouts[$cnt][$pi][1].';--cw:'.$layouts[$cnt][$pi][2].';--ch:'.$layouts[$cnt][$pi][3]]) ?><?php endforeach; ?>
                            <?php if ($cnt === 3): ?><img class="sprig" alt="" src="<?= e(asset('images/ornaments/sprig-blue-tr.png')) ?>" style="position:absolute;right:0;top:0;width:24%;height:auto;opacity:.85;pointer-events:none"><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="collage n4"><?php foreach ($photos as $ph): ?><?= inv_photo($ph, []) ?><?php endforeach; ?></div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
