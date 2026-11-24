<?php /* Halaman "undangan tidak tersedia": link salah/nonaktif (404) atau undangan belum dibuka/offline (503). */ ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Undangan tidak tersedia</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/invitation.css')) ?>">
</head>
<body class="inv" style="background:var(--cream)">
    <main class="unav"><div class="box">
        <h1>Undangan</h1>
        <p>
            <?php if (($reason ?? null) === 'offline'): ?>
                Undangan ini belum dibuka. Silakan kembali lagi nanti.
            <?php else: ?>
                Link undangan tidak valid atau sudah tidak aktif. Silakan hubungi pengundang.
            <?php endif; ?>
        </p>
    </div></main>
</body>
</html>
