<?php /* Section Pray For Us: form RSVP (hanya pada link personal tamu) dan kotak ucapan tamu (dapat digulir). */ ?>
<?php $s = $payload['rsvp'];
    $visible = (int) ($s['wishes_visible_count'] ?? 2);
    $wishes = array_values($wishes ?? []);
    $existing = $guest['response'] ?? null; // RSVP tamu sebelumnya (form terisi ulang)
    $guestHidden = $existing['is_hidden'] ?? null;
    $rsvpOk = session_flash('rsvp_status');   // pesan sukses setelah kirim tanpa JavaScript ?>
<section class="rsvp" id="rsvp" data-section="rsvp">
    <?= inv_photo($s['background'], ['class' => 'bgph']) ?>
    <div class="tint"></div>
    <div class="line d"></div>
    <h2 class="ttl rv" style="font-family:var(--f-script)"><?= e($s['title']) ?></h2>
    <div class="stage">
        <div class="env rv">
            <img class="flap" alt="" src="<?= e(asset('images/ornaments/env-back.png')) ?>">
            <img class="pocket" alt="" src="<?= e(asset('images/ornaments/env-front.png')) ?>">
            <?php if ($guest): ?>
                <form class="paperf" method="POST" action="<?= e(url('/undangan/'.$guest['token'].'/rsvp')) ?>" data-rsvp-form data-max="<?= e($guest['max_attendees']) ?>" novalidate>
                    <?= csrf_field() ?>
                    <div class="fld name"><label for="rs-name"><?= e($s['name_label']) ?></label><input id="rs-name" type="text" value="<?= e($guest['name']) ?>" readonly aria-readonly="true"></div>
                    <div class="fld pill"><span class="lb"><?= e($s['attendance_label']) ?></span></div>
                    <div class="fld pill"><select name="attendance" id="rs-att" required aria-label="<?= e($s['attendance_choice_label']) ?>">
                        <option value=""><?= e($s['attendance_choice_label']) ?></option>
                        <option value="hadir" <?= (($existing['attendance'] ?? null) === 'hadir') ? 'selected' : '' ?>><?= e($s['attend_yes_label']) ?></option>
                        <option value="tidak_hadir" <?= (($existing['attendance'] ?? null) === 'tidak_hadir') ? 'selected' : '' ?>><?= e($s['attend_no_label']) ?></option>
                    </select></div>
                    <div class="fld pill count <?= e(($existing['attendance'] ?? null) === 'hadir' ? 'on' : '') ?>" data-count-box><label for="rs-count"><?= e($s['count_label']) ?></label>
                        <input id="rs-count" name="attendee_count" type="number" min="1" max="<?= e($guest['max_attendees']) ?>" value="<?= e($existing && $existing['attendee_count'] ? $existing['attendee_count'] : 1) ?>"></div>
                    <div class="fld msg"><label for="rs-msg"><?= e($s['message_label']) ?></label><textarea id="rs-msg" name="message" maxlength="500" rows="4"><?= e(old('message', $existing['message'] ?? null)) ?></textarea></div>
                    <button type="submit" class="confirm"><?= e($s['button_text']) ?></button>
                    <p class="rs-msg <?= e($rsvpOk ? 'ok' : '') ?>" role="status" aria-live="polite" data-rs-msg data-ok="<?= e($s['success_text']) ?>"><?= e($rsvpOk ? $s['success_text'] : '') ?></p>
                    <?php foreach (array_merge(...array_values(validation_errors() ?: [[]])) as $error): ?><p class="rs-msg err" role="alert"><?= e($error) ?></p><?php endforeach; ?>
                </form>
            <?php else: ?>
                <div class="paperf">
                    <div class="fld name"><label><?= e($s['name_label']) ?></label></div>
                    <div class="fld pill"><span class="lb"><?= e($s['attendance_label']) ?></span></div>
                    <div class="fld pill"><span class="lb"><?= e($s['attendance_choice_label']) ?></span></div>
                    <div class="fld msg"><label><?= e($s['message_label']) ?></label><div style="height:calc(60*var(--u))"></div></div>
                    <p class="rs-gate"><?= e($preview ? 'Form aktif pada link undangan personal tamu.' : 'Buka undangan melalui link personal Anda untuk mengonfirmasi kehadiran.') ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="airmail rv" id="wishes">
            <?php /* Semua ucapan berada di dalam kotak yang dapat digulir; tinggi kotak = jumlah ucapan yang terlihat (dashboard). */ ?>
            <div class="wish-list" data-wish-list style="--vis:<?= e($visible) ?>" tabindex="0" aria-label="Daftar ucapan tamu">
                <?php foreach ($wishes as $w): ?>
                    <div class="wish" data-wish data-key="<?= e($w['key']) ?>"><span class="wn"><?= e($w['name']) ?></span><p><?= e($w['message']) ?></p></div>
                <?php endforeach; ?>
                <?php for ($i = count($wishes); $i < $visible; $i++): ?>
                    <div class="wish"><span class="wn"><?= e($s['wishes_title']) ?></span></div>
                <?php endfor; ?>
            </div>
            <p class="wish-hint" data-wish-hint hidden>Gulir untuk melihat ucapan lainnya</p>
        </div>
    </div>
</section>
