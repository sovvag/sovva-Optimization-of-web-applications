<?php
$val = function ($name) use ($old, $user) {
    if (isset($old[$name])) {
        return e($old[$name]);
    }
    return isset($user[$name]) ? e($user[$name]) : '';
};
$err = function ($name) use ($errors) {
    if (isset($errors[$name])) {
        echo '<span class="form-error">' . e($errors[$name]) . '</span>';
    }
};
?>
<div class="page-head-row">
    <div>
        <h1>Мой профиль</h1>
        <p class="muted"><?= e($user['email']) ?></p>
    </div>
</div>

<div class="profile-grid">
    <form class="panel" method="post" action="<?= url('/profile') ?>" enctype="multipart/form-data" novalidate>
        <?= Csrf::field() ?>

        <div class="avatar-row">
            <div class="avatar-box">
<?php if (avatar_url($user) !== ''): ?>
                <img id="avatar-preview" class="avatar-big" src="<?= e(avatar_url($user)) ?>" alt="Ваше фото">
<?php else: ?>
                <img id="avatar-preview" class="avatar-big" alt="Ваше фото" hidden>
                <div id="avatar-letters" class="avatar-big avatar-letters"><?= e(initials($user)) ?></div>
<?php endif; ?>
            </div>
            <div class="avatar-controls">
                <label class="btn btn-ghost btn-small file-btn">
                    Выбрать фото
                    <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png">
                </label>
                <p class="muted small">JPG или PNG, до 2 МБ. Фото будет обрезано до квадрата.</p>
                <p class="form-error" id="avatar-js-error" hidden></p>
                <?php $err('avatar'); ?>
<?php if (avatar_url($user) !== ''): ?>
                <label class="check"><input type="checkbox" name="remove_avatar" value="1"> Удалить текущее фото</label>
<?php endif; ?>
            </div>
        </div>

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
            <div class="form-full role-row">
                <span class="muted">Роль в системе</span>
                <span class="badge"><?= e($user['role_title']) ?></span>
                <span class="muted small">Роль может изменить только системный администратор.</span>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>

    <form class="panel" method="post" action="<?= url('/profile/password') ?>" novalidate>
        <?= Csrf::field() ?>
        <h2>Смена пароля</h2>
        <div class="form-grid one-col">
            <label>Текущий пароль
                <input type="password" name="current_password" maxlength="64" autocomplete="current-password" required>
            </label>
            <label>Новый пароль
                <input type="password" name="new_password" maxlength="64" autocomplete="new-password" required>
            </label>
            <label>Повторите новый пароль
                <input type="password" name="new_password2" maxlength="64" autocomplete="new-password" required>
            </label>
        </div>
        <?php $err('password'); ?>
        <p class="muted small">От 8 символов: строчная и заглавная буква и цифра.</p>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Изменить пароль</button>
        </div>
    </form>
</div>
