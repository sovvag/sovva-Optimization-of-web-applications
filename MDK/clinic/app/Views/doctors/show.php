<?php
$skills = array_filter(array_map('trim', explode(',', (string)$doctor['skills'])));

// Микроразметка для поисковиков
$schema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Physician',
    'name'        => full_name($doctor),
    'description' => $doctor['specialty'],
    'url'         => absolute_url('/doctors/' . $doctor['slug']),
];
if (!empty($doctor['photo_path'])) {
    $schema['image'] = absolute_url('/' . $doctor['photo_path']);
}
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>

<nav class="breadcrumbs" aria-label="Навигация">
    <a href="<?= url('/') ?>">Главная</a> / <a href="<?= url('/doctors') ?>">Врачи</a> / <?= e(short_name($doctor)) ?>
</nav>

<article class="doctor-page">
    <div class="doctor-photo">
<?php if (!empty($doctor['photo_path'])): ?>
        <img src="<?= e(url($doctor['photo_path'])) ?>" alt="<?= e(full_name($doctor)) ?>">
<?php else: ?>
        <div class="dc-ph-body"></div><div class="dc-ph-head"></div>
<?php endif; ?>
    </div>

    <div class="doctor-main">
        <span class="badge"><?= e($doctor['specialty']) ?></span>
        <h1><?= e(full_name($doctor)) ?></h1>

        <div class="facts">
            <div class="fact"><span>Стаж</span><strong><?= e(years_text($doctor['experience_years'])) ?></strong></div>
            <div class="fact"><span>Приём</span><strong><?= e(category_label($doctor['patient_category'])) ?></strong></div>
            <div class="fact"><span>Стоимость</span><strong><?= number_format($doctor['price'], 0, ',', ' ') ?> &#8381;</strong></div>
        </div>
    </div>

    <div class="doctor-skills">
        <h2>Профессиональные навыки</h2>
        <div class="chips">
<?php foreach ($skills as $skill): ?>
            <span class="chip"><?= e($skill) ?></span>
<?php endforeach; ?>
        </div>

        <div class="doctor-actions">
<?php if (!Auth::check()): ?>
            <a class="btn btn-primary btn-lg" href="<?= url('/register') ?>">Записаться на приём</a>
            <p class="muted small">Регистрация занимает около минуты.</p>
<?php elseif (Auth::can('appointment.create')): ?>
            <button type="button" class="btn btn-primary btn-lg" id="booking-open" data-goal="booking_open">Записаться на приём</button>
<?php endif; ?>
        </div>
    </div>
</article>

<?php if (Auth::can('appointment.create')): ?>
<?php $initials = mb_substr($doctor['last_name'], 0, 1) . mb_substr($doctor['first_name'], 0, 1); ?>
<!-- Окно записи: календарь и свободное время -->
<div class="modal" id="booking-modal" hidden
     data-api="<?= e(url('/api/slots')) ?>" data-post="<?= e(url('/appointments')) ?>"
     data-mine="<?= e(url('/my-appointments')) ?>" data-doctor-id="<?= (int)$doctor['id'] ?>">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-card modal-lg" role="dialog" aria-modal="true" aria-labelledby="booking-title">
        <div class="modal-head">
            <h2 id="booking-title">Запись на приём</h2>
            <button type="button" class="modal-close" data-close aria-label="Закрыть">&times;</button>
        </div>

        <div class="booking-grid">
            <div class="booking-doctor">
<?php if (!empty($doctor['photo_path'])): ?>
                <img class="avatar" src="<?= e(url($doctor['photo_path'])) ?>" alt="">
<?php else: ?>
                <div class="avatar avatar-letters"><?= e($initials) ?></div>
<?php endif; ?>
                <p class="bd-name"><?= e(full_name($doctor)) ?></p>
                <p class="muted small"><?= e($doctor['specialty']) ?></p>
                <p class="bd-price"><?= number_format((float)$doctor['price'], 0, ',', ' ') ?> &#8381;</p>
            </div>

            <div class="booking-calendar">
                <div class="cal-head">
                    <button type="button" class="cal-nav" id="cal-prev" aria-label="Предыдущий месяц">&lsaquo;</button>
                    <span class="cal-title" id="cal-title"></span>
                    <button type="button" class="cal-nav" id="cal-next" aria-label="Следующий месяц">&rsaquo;</button>
                </div>
                <div class="cal-week"><span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span><span>Сб</span><span>Вс</span></div>
                <div class="cal-grid" id="cal-grid"></div>
            </div>

            <div class="booking-slots">
                <p class="muted small" id="slots-title">Выберите день в календаре</p>
                <div class="slots-list" id="slots-list"></div>
            </div>
        </div>

        <p class="booking-msg" id="booking-msg" hidden></p>

        <div class="modal-foot">
            <span class="muted" id="booking-summary"></span>
            <button type="button" class="btn btn-primary" id="booking-submit" disabled>Отправить заявку</button>
        </div>
    </div>
</div>
<?php endif; ?>
