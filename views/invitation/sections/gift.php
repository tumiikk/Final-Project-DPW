<?php
/*
 * Section Kirim Hadiah (setelah Doa & Ucapan).
 * Judul + kalimat pembuka, lalu dua kartu krem berbingkai emas:
 *   - Transfer Bank : daftar rekening aktif (logo/nama bank, nomor, pemilik, tombol salin)
 *   - QRIS          : gambar QRIS utuh + keterangan
 * Desktop: kedua kartu berdampingan (bank kiri, QRIS kanan). Mobile: bertumpuk (bank atas, QRIS bawah).
 * Bila hanya satu kartu yang berisi, kartu itu tampil di tengah. Di preview dashboard kartu kosong tetap tampil sebagai petunjuk.
 */
$s = $payload['gift'];
$cards = App\Support\Inv::giftCards($s);
$media = new App\Services\MediaService();
$showBank = $cards['bank'] || ($preview && ($s['show_bank'] ?? true));
$showQris = $cards['qris'] || ($preview && ($s['show_qris'] ?? true));
$copyText = ($s['copy_text'] ?? null) ?: 'Salin Nomor Rekening';
?>
<?php if (App\Support\Inv::giftVisible($payload) || $preview): ?>
<section class="gift" id="gift" data-section="gift">
    <img class="o gf-orn tr" alt="" src="<?= e(asset('images/ornaments/sprig-gold-tr.png')) ?>">
    <img class="o gf-orn bl" alt="" src="<?= e(asset('images/ornaments/sprig-gold-bl.png')) ?>">
    <h2 class="gf-ttl rv"><?= e($s['title']) ?></h2>
    <img class="gf-div rv" alt="" src="<?= e(asset('images/ornaments/div-fancy-gold.png')) ?>">
    <?php if (filled($s['description'] ?? null)): ?><p class="gf-lead rv"><?= e($s['description']) ?></p><?php endif; ?>

    <div class="gf-cards <?= ($showBank && $showQris) ? 'two' : 'one' ?>">
        <?php if ($showBank): ?>
            <article class="gf-card rv" aria-label="<?= e($s['bank_title'] ?: 'Transfer Bank') ?>">
                <div class="gf-hd">
                    <svg class="gf-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9.5 12 4l9 5.5M4.5 10v8M9 10v8M15 10v8M19.5 10v8M3 20h18"/></svg>
                    <h3><?= e($s['bank_title'] ?: 'Transfer Bank') ?></h3>
                    <img class="gf-hdiv" alt="" src="<?= e(asset('images/ornaments/div-diamond-b.png')) ?>">
                </div>
                <div class="gf-acc">
                    <?php foreach ($cards['accounts'] as $a): ?>
                        <div class="gf-item">
                            <?php if (! empty($a['logo']['path'])): ?>
                                <img class="gf-logo" src="<?= e($media->url($a['logo'])) ?>" alt="<?= e($a['bank_name']) ?>" loading="lazy" decoding="async">
                            <?php else: ?>
                                <div class="gf-bank"><?= e($a['bank_name']) ?></div>
                            <?php endif; ?>
                            <div class="gf-no"><?= e($a['account_number']) ?></div>
                            <div class="gf-rc">a.n. <?= e($a['recipient']) ?></div>
                            <?php if (filled($a['note'] ?? null)): ?><div class="gf-nt"><?= e($a['note']) ?></div><?php endif; ?>
                            <button type="button" class="gf-copy" data-copy="<?= e($a['account_number']) ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/></svg><span><?= e($copyText) ?></span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <?php if (! $cards['accounts']): ?><p class="gf-empty" data-ph>Belum ada rekening aktif. Tambahkan di dashboard.</p><?php endif; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if ($showQris): ?>
            <article class="gf-card qris rv" aria-label="<?= e($s['qris_title'] ?: 'QRIS') ?>">
                <div class="gf-hd">
                    <svg class="gf-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2zM6.5 6.5h1v1h-1zM16.5 6.5h1v1h-1zM6.5 16.5h1v1h-1z"/></svg>
                    <h3><?= e($s['qris_title'] ?: 'QRIS') ?></h3>
                    <img class="gf-hdiv" alt="" src="<?= e(asset('images/ornaments/div-diamond-b.png')) ?>">
                </div>
                <div class="gf-qr">
                    <?php if ($cards['qris']): ?>
                        <img src="<?= e($media->url($s['qris_image'])) ?>" alt="<?= e($s['qris_image']['alt'] ?? 'Kode QRIS') ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                        <span class="gf-qr-empty" data-ph>Unggah gambar QRIS di dashboard</span>
                    <?php endif; ?>
                </div>
                <?php if (filled($s['qris_caption'] ?? null)): ?><p class="gf-cap"><?= e($s['qris_caption']) ?></p><?php endif; ?>
            </article>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
