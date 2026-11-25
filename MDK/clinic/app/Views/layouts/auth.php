<?php
// Макет для страниц входа и регистрации (матовое стекло на цветном фоне)
$title = isset($title) ? $title : APP_NAME;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="auth-body">
<div class="auth-scene">
    <div class="glass-card">
<?php require APP_PATH . '/Views/layouts/flash.php'; ?>
<?= $content ?>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
