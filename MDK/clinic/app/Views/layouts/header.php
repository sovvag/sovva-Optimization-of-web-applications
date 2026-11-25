<?php // Шапка для гостей (вошедшим пользователям вместо неё показывается боковая панель) ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= url('/') ?>">
            <?= icon('eye', 26) ?>
            <span><?= e(APP_NAME) ?></span>
        </a>

        <nav class="nav" aria-label="Основное меню">
            <a href="<?= url('/') ?>">Главная</a>
            <a href="<?= url('/doctors') ?>">Врачи</a>
            <a href="<?= url('/articles') ?>">Статьи</a>
        </nav>

        <div class="nav-user">
            <a class="btn btn-ghost" href="<?= url('/login') ?>">Войти</a>
            <a class="btn btn-primary" href="<?= url('/register') ?>">Регистрация</a>
        </div>
    </div>
</header>
