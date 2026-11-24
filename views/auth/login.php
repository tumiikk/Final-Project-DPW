<?php
/* Halaman login admin (desain mockup: bunga, burung, cupid, input bergambar). Pesan error login tampil di bawah input. */
$loginError = validation_errors()['login'][0] ?? null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/admin.css')) ?>">
</head>
<body class="adm login-page">
<main class="login">
    <img class="l-fl tr" alt="" src="<?= e(asset('images/ornaments/login-flower-tr.png')) ?>">
    <img class="l-fl bl" alt="" src="<?= e(asset('images/ornaments/login-flower-bl.png')) ?>">
    <img class="l-birds" alt="" src="<?= e(asset('images/ornaments/birds-ribbon.png')) ?>">
    <img class="l-cupid" alt="" src="<?= e(asset('images/ornaments/cupid.png')) ?>">
    <h1 class="script">Admin<br>Login</h1>
    <img class="l-div" alt="" src="<?= e(asset('images/ornaments/div-diamond.png')) ?>">
    <form method="POST" action="<?= e(url('/admin/login')) ?>" class="l-form" novalidate>
        <?= csrf_field() ?>
        <label class="l-in"><img alt="" src="<?= e(asset('images/ornaments/icon-mail.png')) ?>"><span class="sr">Email atau username</span><input name="login" value="<?= e(old('login')) ?>" placeholder="Email/Username" required autofocus autocomplete="username"></label>
        <label class="l-in"><img alt="" src="<?= e(asset('images/ornaments/icon-lock.png')) ?>"><span class="sr">Password</span><input name="password" type="password" id="pw" placeholder="Password" required autocomplete="current-password"><button type="button" class="eye" id="eye" aria-label="Tampilkan password"><img alt="" src="<?= e(asset('images/ornaments/icon-eye.png')) ?>"></button></label>
        <?php if ($loginError): ?><p class="err" role="alert"><?= e($loginError) ?></p><?php endif; ?>
        <button type="submit" class="l-btn"><i>◆</i> Login <i>◆</i></button>
    </form>
</main>
<script>/* Tombol mata: tampilkan / sembunyikan password */ document.getElementById('eye').addEventListener('click',function(){var p=document.getElementById('pw');p.type=p.type==='password'?'text':'password';});</script>
</body>
</html>
