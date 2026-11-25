<?php
$labels = [
    'appointment.create' => 'Заявка пациента', 'appointment.create_by_admin' => 'Запись администратором',
    'appointment.confirm' => 'Заявка одобрена', 'appointment.reject' => 'Заявка отклонена',
    'appointment.move' => 'Запись перенесена', 'appointment.update' => 'Запись изменена',
    'appointment.delete' => 'Запись удалена', 'appointment.cancel_by_patient' => 'Отмена пациентом',
    'patient.create' => 'Пациент добавлен', 'patient.update' => 'Пациент изменён', 'patient.delete' => 'Карта пациента удалена',
    'doctor.create' => 'Врач добавлен', 'doctor.update' => 'Врач изменён',
    'schedule.create' => 'Смены добавлены', 'schedule.update' => 'Смена изменена', 'schedule.delete' => 'Смена удалена',
    'profile.update' => 'Профиль изменён', 'profile.password' => 'Смена пароля',
    'roles.update' => 'Права ролей изменены', 'user.create' => 'Аккаунт создан', 'user.update' => 'Аккаунт изменён',
    'user.role_change' => 'Роль изменена', 'user.activate' => 'Аккаунт разблокирован',
    'user.deactivate' => 'Аккаунт заблокирован', 'user.password_reset' => 'Пароль сброшен',
];
?>
<div class="page-head-row">
    <div>
        <h1>Журнал действий</h1>
        <p class="muted">Последние 200 действий пользователей. Записи журнала изменить нельзя.</p>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Когда</th><th>Кто</th><th>Действие</th><th>Подробности</th><th>IP</th></tr></thead>
        <tbody>
<?php foreach ($entries as $en): ?>
            <tr>
                <td class="nowrap"><?= e(date('d.m.Y H:i', strtotime($en['created_at']))) ?></td>
                <td><?= $en['email'] ? e(short_name($en)) . '<br><span class="muted small">' . e($en['email']) . '</span>' : '<span class="muted">неизвестно</span>' ?></td>
                <td><?= e(isset($labels[$en['action']]) ? $labels[$en['action']] : $en['action']) ?></td>
                <td class="small"><?= e($en['details']) ?></td>
                <td class="small muted"><?= e($en['ip']) ?></td>
            </tr>
<?php endforeach; ?>
<?php if (empty($entries)): ?>
            <tr><td colspan="5" class="muted">Журнал пока пуст.</td></tr>
<?php endif; ?>
        </tbody>
    </table>
</div>
