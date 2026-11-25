<?php
// Микроразметка для поисковиков (Schema.org)
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'MedicalClinic',
    'name'     => APP_NAME,
    'url'      => absolute_url('/'),
    'medicalSpecialty' => 'Ophthalmic',
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>

<section class="hero">
    <h1>Забота о зрении <br>с записью онлайн</h1>
    <p class="hero-text">Выберите врача, удобную дату и время. Администратор подтвердит запись,
       а вы увидите её в личном кабинете.</p>
    <div class="hero-actions">
        <a class="btn btn-primary btn-lg" href="<?= url('/doctors') ?>">Записаться онлайн</a>
        <a class="btn btn-ghost btn-lg" href="<?= url('/register') ?>">Создать личный кабинет</a>
    </div>
</section>

<section class="steps">
    <h2>Как записаться</h2>
    <div class="steps-grid">
        <div class="step"><span class="step-num">1</span><h3>Выберите врача</h3>
            <p>Офтальмолог, офтальмохирург или ретинолог. Смотрите стаж и для кого ведётся приём.</p></div>
        <div class="step"><span class="step-num">2</span><h3>Укажите дату и время</h3>
            <p>В календаре видны только свободные ячейки. Занятое время не показывается.</p></div>
        <div class="step"><span class="step-num">3</span><h3>Дождитесь подтверждения</h3>
            <p>Администратор клиники одобрит заявку, и время закрепится за вами.</p></div>
    </div>
    <p class="muted center">Сейчас у нас принимают врачей: <strong><?= (int)$doctorCount ?></strong></p>
</section>
