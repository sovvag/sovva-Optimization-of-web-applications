<div class="page-head-row">
    <div>
        <h1>Аккаунты</h1>
        <p class="muted">Все пользователи сайта: <?= count($users) ?></p>
    </div>
    <a class="btn btn-primary" href="<?= url('/sysadmin/users/create') ?>">Добавить пользователя</a>
</div>

<form class="search-bar" method="get" action="<?= url('/sysadmin/users') ?>">
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Поиск по ФИО или email" maxlength="60">
    <select name="role" aria-label="Роль">
        <option value="0">Все роли</option>
<?php foreach ($roles as $r): ?>
        <option value="<?= (int)$r['id'] ?>"<?= $roleId === (int)$r['id'] ? ' selected' : '' ?>><?= e($r['title']) ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Найти</button>
<?php if ($query !== '' || $roleId > 0): ?>
    <a class="btn btn-ghost" href="<?= url('/sysadmin/users') ?>">Сбросить</a>
<?php endif; ?>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>ФИО</th><th>Email</th><th>Роль</th><th>Статус</th><th>Создан</th><th class="col-actions">Действия</th></tr>
        </thead>
        <tbody>
<?php foreach ($users as $u): ?>
            <tr>
                <td><strong><?= e(full_name($u)) ?></strong></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge"><?= e($u['role_title']) ?></span></td>
                <td>
<?php if ((int)$u['is_active'] === 1): ?>
                    <span class="status status-confirmed">Активен</span>
<?php else: ?>
                    <span class="status status-cancelled">Заблокирован</span>
<?php endif; ?>
                </td>
                <td><?= e(date('d.m.Y', strtotime($u['created_at']))) ?></td>
                <td class="col-actions">
                    <a class="btn btn-ghost btn-small" href="<?= url('/sysadmin/users/' . (int)$u['id'] . '/edit') ?>">Изменить</a>
                </td>
            </tr>
<?php endforeach; ?>
<?php if (empty($users)): ?>
            <tr><td colspan="6" class="muted">Никого не найдено.</td></tr>
<?php endif; ?>
        </tbody>
    </table>
</div>
