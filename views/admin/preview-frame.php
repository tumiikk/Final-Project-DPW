<?php /* Dokumen di dalam iframe Live Preview: memuat CSS undangan lalu menerima HTML section dari editor lewat postMessage. */ ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Preview</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/invitation.css')) ?>">
    <style>html{scroll-behavior:auto}body.inv{overflow:hidden;background:var(--navy)}.is-preview .rv{opacity:1!important;transform:none!important}.music-btn{display:none!important}</style>
</head>
<body class="inv noanim">
    <div id="mount"><?= $html ?></div>
    <script>
        // Memberi tahu halaman editor tinggi konten agar iframe menyesuaikan ukurannya
        function fit(){ parent.postMessage({ type: 'prev-height', h: document.documentElement.scrollHeight }, location.origin); }
        // Menerima HTML preview terbaru dari editor (hanya dari origin yang sama)
        window.addEventListener('message', function (e) {
            if (e.origin !== location.origin || !e.data || e.data.type !== 'render') return;
            document.getElementById('mount').innerHTML = e.data.html;
            requestAnimationFrame(fit);
        });
        window.addEventListener('load', function () { fit(); parent.postMessage({ type: 'prev-ready' }, location.origin); });
        window.addEventListener('resize', fit);
        document.addEventListener('load', fit, true);
        // Di preview, link/tombol/form tidak dijalankan
        document.addEventListener('click', function (e) { if (e.target.closest('a,button,[type=submit]')) e.preventDefault(); }, true);
        document.addEventListener('submit', function (e) { e.preventDefault(); }, true);
    </script>
</body>
</html>
