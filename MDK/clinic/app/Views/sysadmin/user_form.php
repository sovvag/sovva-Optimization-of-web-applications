<?php
$val = function ($name) use ($old, $target) {
    if (isset($old[$name])) {
        return e($old[$name]);
    }
    return $target && isset($target[$name]) ? e($target[$name]) : '';
};
$err = function ($name) use ($errors) {
    if (isset($errors[$name])) {
        echo '<span class="form-error">' . e($errors[$name]) . '</span>';
    }
};
$selRole = isset($old['role_id']) ? (int)$old['role_id'] : ($target ? (int)$target['role_id'] : 0);
$active  = isset($old['is_active']) ? (int)$old['is_active'] : ($target ? (int)$target['is_active'] : 1);
?>
<div class="page-head-row">
    <div>
        <h1><?= $target ? 'Пользователь' : 'Новый пользователь' ?></h1>
        <p class="muted">Поля со звёздочкой обязательны</p>
    </div>
    <a class="btn btn-ghost" href="<?= url('/sysadmin/users') ?>">&larr; К списку</a>
</div>

<?php if (isset($errors['form'])): ?>
    <div class="alert alert-error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="panel form-panel" method="post" action="<?= url($formAction) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="form-grid">
        <label>Фамилия *
            <input name="last_name" maxlength="60" required value="<?= $val('last_name') ?>">
            <?php $err('last_name'); ?>
        </label>
        <label>Имя *
            <input name="first_name" maxlength="60" required value="<?= $val('first_name') ?>">
            <?php $err('first_name'); ?>
        </label>
        <label>Отчество
            <input name="middle_name" maxlength="60" value="<?= $val('middle_name') ?>">
            <?php $err('middle_name'); ?>
        </label>
        <label>Дата рождения
            <input type="date" name="birth_date" min="1900-01-01" max="<?= date('Y-m-d') ?>" value="<?= $val('birth_date') ?>">
            <?php $err('birth_date'); ?>
        </label>
        <label class="form-full">Email (логин) *
            <input type="email" name="email" maxlength="150" required autocomplete="off" value="<?= $val('email') ?>">
            <?php $err('email'); ?>
        </label>
        <label>Роль *
            <select name="role_id"<?= $isSelf ? ' disabled' : '' ?>>
<?php foreach ($roles as $r): ?>
                <option value="<?= (int)$r['id'] ?>"<?= $selRole === (int)$r['id'] ? ' selected' : '' ?>><?= e($r['title']) ?></option>
<?php endforeach; ?>
            </select>
            <?php $err('role_id'); ?>
        </label>
        <div class="role-active">
            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>> Аккаунт активен</label>
<?php if ($isSelf): ?>
            <span class="muted small">Свою роль и активность менять нельзя.</span>
<?php endif; ?>
        </div>
        <label class="form-full"><?= $target ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Временный пароль *' ?>
            <input type="password" name="password" maxlength="64" autocomplete="new-password"<?= $target ? '' : ' required' ?>>
            <?php $err('password'); ?>
            <span class="muted small">От 8 символов: строчная и заглавная буква и цифра.</span>
        </label>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Сохранить</button>
        <a class="btn btn-ghost" href="<?= url('/sysadmin/users') ?>">Отмена</a>
    </div>
</form>
