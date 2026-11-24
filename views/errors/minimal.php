<?php
/*
 * Halaman error sederhana (404, 403, 419, 429, 500, ...), meniru halaman error bawaan Laravel.
 * Detail exception hanya ditampilkan bila APP_DEBUG=true.
 */
$titles = [401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 413 => 'Content Too Large',
    419 => 'Page Expired', 422 => 'Unprocessable Content', 429 => 'Too Many Requests', 500 => 'Server Error', 503 => 'Service Unavailable'];
$text = $titles[$code] ?? 'Error';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($text) ?></title>
    <style>
        body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#f7fafc;color:#a0aec0}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center}
        .code{padding:0 16px;border-right:1px solid #a0aec0;font-size:18px;letter-spacing:.05em}
        .msg{margin-left:16px;font-size:18px;text-transform:uppercase;letter-spacing:.05em}
        pre{max-width:90vw;overflow:auto;background:#fff;color:#a32f2f;padding:16px;font-size:13px;margin:0 auto 40px}
    </style>
</head>
<body>
    <div class="wrap"><div class="code"><?= e($code) ?></div><div class="msg"><?= e($text) ?></div></div>
    <?php if (! empty($debug)): ?>
        <pre><?= e(get_class($debug).': '.$debug->getMessage()."\n".$debug->getFile().':'.$debug->getLine()."\n\n".$debug->getTraceAsString()) ?></pre>
    <?php endif; ?>
</body>
</html>
