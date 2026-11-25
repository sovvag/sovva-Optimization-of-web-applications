<?php
// Боковая панель: иконки, при наведении раскрывается и показывает названия.
// Пункты меню зависят от ПРАВ пользователя. Но скрытие пункта - не защита:
// доступ к страницам всё равно проверяется на сервере (см. config/routes.php).
$items = [];
if (Auth::can('appointment.create')) {
    $items[] = ['Врачи', '/doctors', 'eye'];
}
if (Auth::can('appointment.view_own')) {
    $items[] = ['Записи', '/my-appointments', 'chk'];
}
if (Auth::can('schedule.view')) {
    $items[] = ['Расписание', '/schedule', 'cal'];
}
if (Auth::can('patients.manage')) {
    $items[] = ['Пациенты', '/patients', 'fold'];
}
if (Auth::can('work_schedule.manage')) {
    $items[] = ['График', '/manager/work-schedule', 'clock'];
}
if (Auth::can('doctors.manage')) {
    $items[] = ['Врачи', '/manager/doctors', 'eye'];
}
if (Auth::can('reports.view')) {
    $items[] = ['Отчёты', '/manager/reports', 'chart'];
}
if (Auth::can('roles.manage')) {
    $items[] = ['Права', '/sysadmin/roles', 'lock'];
}
if (Auth::can('users.manage')) {
    $items[] = ['Аккаунты', '/sysadmin/users', 'users'];
    $items[] = ['Журнал', '/sysadmin/audit', 'clock'];
}
?>
<nav class="rail" aria-label="Главное меню">
    <a class="rail-brand" href="<?= url('/') ?>" title="<?= e(APP_NAME) ?>"><?= icon('eye', 26) ?></a>

<?php foreach ($items as $item): ?>
    <a class="rail-item<?= nav_active($item[1]) ? ' active' : '' ?>" href="<?= url($item[1]) ?>" title="<?= e($item[0]) ?>">
        <?= glass_icon($item[2]) ?>
        <span class="rail-label"><?= e($item[0]) ?></span>
    </a>
<?php endforeach; ?>

    <div class="rail-space"></div>

    <a class="rail-item<?= nav_active('/profile') ? ' active' : '' ?>" href="<?= url('/profile') ?>" title="Профиль">
<?php if (avatar_url(Auth::user()) !== ''): ?>
        <span class="rail-avatar"><img src="<?= e(avatar_url(Auth::user())) ?>" alt=""></span>
<?php else: ?>
        <?= glass_icon('user') ?>
<?php endif; ?>
        <span class="rail-label">Профиль</span>
    </a>

    <form method="post" action="<?= url('/logout') ?>" class="rail-form">
        <?= Csrf::field() ?>
        <button type="submit" class="rail-item" title="Выйти">
            <?= glass_icon('out') ?>
            <span class="rail-label">Выход</span>
        </button>
    </form>
</nav>
