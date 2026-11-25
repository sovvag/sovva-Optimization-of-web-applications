<?php
// Значение поля: сначала то, что ввели (после ошибки), иначе данные из базы
function pf_value($old, $patient, $name)
{
    if (isset($old[$name])) {
        return e($old[$name]);
    }
    return $patient && isset($patient[$name]) ? e($patient[$name]) : '';
}
function pf_error($errors, $name)
{
    if (isset($errors[$name])) {
        echo '<span class="form-error">' . e($errors[$name]) . '</span>';
    }
}
?>
<div class="page-head-row">
    <div>
        <h1><?= $patient ? 'Карточка пациента' : 'Новый пациент' ?></h1>
        <p class="muted">Поля со звёздочкой обязательны</p>
    </div>
    <a class="btn btn-ghost" href="<?= url('/patients') ?>">&larr; К списку</a>
</div>

<form class="panel form-panel" method="post" action="<?= url($formAction) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="form-grid">
        <label>Фамилия *
            <input name="last_name" maxlength="60" required value="<?= pf_value($old, $patient, 'last_name') ?>">
            <?php pf_error($errors, 'last_name'); ?>
        </label>
        <label>Имя *
            <input name="first_name" maxlength="60" required value="<?= pf_value($old, $patient, 'first_name') ?>">
            <?php pf_error($errors, 'first_name'); ?>
        </label>
        <label>Отчество
            <input name="middle_name" maxlength="60" value="<?= pf_value($old, $patient, 'middle_name') ?>">
            <?php pf_error($errors, 'middle_name'); ?>
        </label>
        <label>Дата рождения
            <input type="date" name="birth_date" min="1900-01-01" max="<?= date('Y-m-d') ?>" value="<?= pf_value($old, $patient, 'birth_date') ?>">
            <?php pf_error($errors, 'birth_date'); ?>
        </label>
        <label>Телефон
            <input name="phone" maxlength="20" placeholder="+7 900 000-00-00" value="<?= pf_value($old, $patient, 'phone') ?>">
            <?php pf_error($errors, 'phone'); ?>
        </label>
        <label class="form-full">Адрес
            <input name="address" maxlength="255" value="<?= pf_value($old, $patient, 'address') ?>">
            <?php pf_error($errors, 'address'); ?>
        </label>
    </div>

<?php if ($patient && !empty($patient['email'])): ?>
    <p class="muted small">Аккаунт на сайте: <?= e($patient['email']) ?></p>
<?php endif; ?>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Сохранить</button>
        <a class="btn btn-ghost" href="<?= url('/patients') ?>">Отмена</a>
    </div>
</form>
