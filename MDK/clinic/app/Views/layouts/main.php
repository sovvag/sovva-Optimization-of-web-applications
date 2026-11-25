<?php
// Общий макет. Вошедшему пользователю показываем боковую панель с иконками,
// гостю - обычную шапку сайта. Переменные приходят из контроллера.
$title       = isset($title) ? $title : APP_NAME;
$description = isset($description) ? $description : '';
$canonical   = isset($canonical) ? $canonical : '';
$noindex     = !empty($noindex);
$styles      = isset($styles) ? $styles : [];     // дополнительные css-файлы страницы
$scripts     = isset($scripts) ? $scripts : [];   // дополнительные js-файлы страницы
$loggedIn    = Auth::check();
// Метрика только на публичных страницах и только для гостей и пациентов
$ymId = (int)env('YM_COUNTER_ID', 0);
$useMetrika = $ymId > 0 && !$noindex && (!$loggedIn || Auth::role() === 'patient');
$ymGoal = pull('goal');

// Всплывающие уведомления нужны только тому, кто может одобрять заявки
$bodyData = '';
if (Auth::can('appointment.approve')) {
    $bodyData = ' data-notify-url="' . e(url('/api/notifications')) . '"'
              . ' data-status-url="' . e(url('/schedule/appointments')) . '"'
              . ' data-schedule-url="' . e(url('/schedule')) . '"';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
<?php if ($description !== ''): ?>
    <meta name="description" content="<?= e($description) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($noindex): ?>
    <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<?php if ($canonical !== ''): ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($ogImage)): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="<?= e(APP_NAME) ?>">
<?php if ($loggedIn): ?>
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<?php endif; ?>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<?php foreach ($styles as $css): ?>
    <link rel="stylesheet" href="<?= asset($css) ?>">
<?php endforeach; ?>
<?php if ($useMetrika && $ymGoal): ?>
    <meta name="ym-goal" content="<?= e($ymGoal) ?>">
<?php endif; ?>
</head>
<body class="<?= $loggedIn ? 'app-body' : '' ?>"<?= $bodyData ?>>
<?php if ($loggedIn): ?>
<?php require APP_PATH . '/Views/layouts/sidebar.php'; ?>
<div class="app-dim"></div>
<main class="app-main">
<?php require APP_PATH . '/Views/layouts/flash.php'; ?>
<?= $content ?>
</main>
<?php else: ?>
<?php require APP_PATH . '/Views/layouts/header.php'; ?>
<main class="container">
<?php require APP_PATH . '/Views/layouts/flash.php'; ?>
<?= $content ?>
</main>
<?php require APP_PATH . '/Views/layouts/footer.php'; ?>
<?php endif; ?>

<script src="<?= asset('js/app.js') ?>"></script>
<?php if ($useMetrika): ?>
<script src="<?= asset('js/metrika.js') ?>" data-id="<?= $ymId ?>"></script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?= $ymId ?>" style="position:absolute;left:-9999px;" alt=""></div></noscript>
<?php endif; ?>
<?php if (Auth::can('appointment.approve')): ?>
<script src="<?= asset('js/staff.js') ?>"></script>
<?php endif; ?>
<?php foreach ($scripts as $js): ?>
<script src="<?= asset($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
