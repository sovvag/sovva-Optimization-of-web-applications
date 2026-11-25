<div class="gc-top">
    <a class="logo" href="<?= url('/') ?>"><?= icon('eye', 24) ?><span><?= e(APP_NAME) ?></span></a>
    <a href="<?= url('/register') ?>" class="gc-link">Регистрация</a>
</div>

<div class="gc-head">
    <h1>Вход</h1>
</div>

<form method="post" action="<?= url('/login') ?>" novalidate>
    <?= Csrf::field() ?>

<?php if (isset($errors['form'])): ?>
    <p class="field-error"><?= e($errors['form']) ?></p>
<?php endif; ?>

    <label class="field">
        <span class="field-icon"><?= icon('mail') ?></span>
        <input type="email" name="email" placeholder="Электронная почта" required maxlength="150"
               autocomplete="email" value="<?= e(isset($old['email']) ? $old['email'] : '') ?>">
    </label>

    <label class="field">
        <span class="field-icon"><?= icon('key') ?></span>
        <input type="password" name="password" placeholder="Пароль" required maxlength="64"
               autocomplete="current-password">
    </label>

    <div class="gc-bottom">
        <p class="gc-note">Ваши данные защищены и обрабатываются в соответствии с 152-ФЗ.</p>
        <button type="submit" class="go-btn" aria-label="Войти"><?= icon('arrow', 24) ?></button>
    </div>
</form>

<p class="gc-footer">Нет аккаунта? <a href="<?= url('/register') ?>">Зарегистрироваться</a></p>
