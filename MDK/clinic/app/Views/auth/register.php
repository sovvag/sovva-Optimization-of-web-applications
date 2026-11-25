<?php
// Маленький помощник: показать ошибку под полем
function field_error($errors, $name)
{
    if (isset($errors[$name])) {
        echo '<p class="field-error">' . e($errors[$name]) . '</p>';
    }
}
function old_value($old, $name)
{
    return e(isset($old[$name]) ? $old[$name] : '');
}
?>
<div class="gc-top">
    <a class="logo" href="<?= url('/') ?>"><?= icon('eye', 24) ?><span><?= e(APP_NAME) ?></span></a>
    <a href="<?= url('/login') ?>" class="gc-link">Вход</a>
</div>

<div class="gc-head">
    <h1>Регистрация</h1>
</div>

<form method="post" action="<?= url('/register') ?>" id="register-form" novalidate>
    <?= Csrf::field() ?>

    <label class="field">
        <span class="field-icon"><?= icon('user') ?></span>
        <input type="text" name="last_name" placeholder="Фамилия" required maxlength="60"
               autocomplete="family-name" value="<?= old_value($old, 'last_name') ?>">
    </label>
    <?php field_error($errors, 'last_name'); ?>

    <label class="field">
        <span class="field-icon"><?= icon('user') ?></span>
        <input type="text" name="first_name" placeholder="Имя" required maxlength="60"
               autocomplete="given-name" value="<?= old_value($old, 'first_name') ?>">
    </label>
    <?php field_error($errors, 'first_name'); ?>

    <label class="field">
        <span class="field-icon"><?= icon('user') ?></span>
        <input type="text" name="middle_name" placeholder="Отчество (если есть)" maxlength="60"
               autocomplete="additional-name" value="<?= old_value($old, 'middle_name') ?>">
    </label>
    <?php field_error($errors, 'middle_name'); ?>

    <label class="field">
        <span class="field-icon"><?= icon('cal') ?></span>
        <input type="date" name="birth_date" required min="1900-01-01" max="<?= date('Y-m-d') ?>"
               title="Дата рождения" value="<?= old_value($old, 'birth_date') ?>">
    </label>
    <?php field_error($errors, 'birth_date'); ?>

    <label class="field">
        <span class="field-icon"><?= icon('mail') ?></span>
        <input type="email" name="email" placeholder="Электронная почта" required maxlength="150"
               autocomplete="email" value="<?= old_value($old, 'email') ?>">
    </label>
    <?php field_error($errors, 'email'); ?>

    <label class="field">
        <span class="field-icon"><?= icon('key') ?></span>
        <input type="password" name="password" placeholder="Пароль" required maxlength="64"
               autocomplete="new-password">
    </label>
    <?php field_error($errors, 'password'); ?>
    <ul class="pw-hints" id="pw-hints">
        <li data-rule="len">Минимум 8 символов</li>
        <li data-rule="case">Строчная и заглавная буква</li>
        <li data-rule="digit">Цифра</li>
    </ul>

    <label class="field">
        <span class="field-icon"><?= icon('key') ?></span>
        <input type="password" name="password2" placeholder="Повторите пароль" required maxlength="64"
               autocomplete="new-password">
    </label>
    <?php field_error($errors, 'password2'); ?>

    <label class="consent">
        <input type="checkbox" name="consent" value="1">
        <span>Согласен на обработку персональных данных</span>
    </label>
    <?php field_error($errors, 'consent'); ?>

    <p class="field-error" id="js-error" hidden></p>

    <button type="submit" class="btn btn-dark btn-block">Создать аккаунт</button>
</form>

<p class="gc-footer">Уже есть аккаунт? <a href="<?= url('/login') ?>">Войти</a></p>
