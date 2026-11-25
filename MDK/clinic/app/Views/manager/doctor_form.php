<?php
$val = function ($name) use ($old, $doctor) {
    if (isset($old[$name])) {
        return e($old[$name]);
    }
    return $doctor && isset($doctor[$name]) ? e($doctor[$name]) : '';
};
$err = function ($name) use ($errors) {
    if (isset($errors[$name])) {
        echo '<span class="form-error">' . e($errors[$name]) . '</span>';
    }
};
$categories = ['adults' => 'Только взрослые', 'children' => 'Только дети', 'children14_adults' => 'Дети с 14 лет и взрослые'];
$selSpec = isset($old['specialty_id']) ? $old['specialty_id'] : ($doctor ? $doctor['specialty_id'] : '');
$selCat  = isset($old['patient_category']) ? $old['patient_category'] : ($doctor ? $doctor['patient_category'] : 'adults');
$active  = isset($old['is_active']) ? (int)$old['is_active'] : ($doctor ? (int)$doctor['is_active'] : 1);
$photo   = $doctor && !empty($doctor['photo_path']) ? url($doctor['photo_path']) : '';
?>
<div class="page-head-row">
    <div>
        <h1><?= $doctor ? 'Карточка врача' : 'Новый врач' ?></h1>
        <p class="muted">Поля со звёздочкой обязательны</p>
    </div>
    <a class="btn btn-ghost" href="<?= url('/manager/doctors') ?>">&larr; К списку</a>
</div>

<form class="panel form-panel" method="post" action="<?= url($formAction) ?>" enctype="multipart/form-data" novalidate>
    <?= Csrf::field() ?>

    <div class="avatar-row">
        <div class="avatar-box">
            <img id="avatar-preview" class="photo-big"<?= $photo !== '' ? ' src="' . e($photo) . '"' : ' hidden' ?> alt="Фото врача">
<?php if ($photo === ''): ?>
            <div id="avatar-letters" class="photo-big avatar-letters">Фото</div>
<?php endif; ?>
        </div>
        <div class="avatar-controls">
            <label class="btn btn-ghost btn-small file-btn">
                Выбрать фото
                <input type="file" name="photo" id="avatar-input" accept="image/jpeg,image/png">
            </label>
            <p class="muted small">JPG или PNG, до 2 МБ. Фото обрежется до пропорции 3:4.</p>
            <p class="form-error" id="avatar-js-error" hidden></p>
            <?php $err('photo'); ?>
<?php if ($photo !== ''): ?>
            <label class="check"><input type="checkbox" name="remove_photo" value="1"> Удалить текущее фото</label>
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
        <label>Должность *
            <select name="specialty_id" required>
                <option value="">Выберите...</option>
<?php foreach ($specialties as $s): ?>
                <option value="<?= (int)$s['id'] ?>"<?= (string)$selSpec === (string)$s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option>
<?php endforeach; ?>
            </select>
            <?php $err('specialty_id'); ?>
        </label>
        <label>Стаж, лет *
            <input type="number" name="experience_years" min="0" max="70" required value="<?= $val('experience_years') ?>">
            <?php $err('experience_years'); ?>
        </label>
        <label>Кого принимает *
            <select name="patient_category" required>
<?php foreach ($categories as $code => $label): ?>
                <option value="<?= e($code) ?>"<?= $selCat === $code ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
            </select>
            <?php $err('patient_category'); ?>
        </label>
        <label>Стоимость приёма, &#8381; *
            <input name="price" inputmode="decimal" required value="<?= $val('price') ?>">
            <?php $err('price'); ?>
        </label>
        <label class="form-full">Профессиональные навыки (через запятую)
            <textarea name="skills" rows="3" maxlength="1000"><?= $val('skills') ?></textarea>
            <?php $err('skills'); ?>
        </label>
        <div class="form-full">
            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>> Врач работает (показывать на сайте)</label>
            <?php $err('is_active'); ?>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Сохранить</button>
        <a class="btn btn-ghost" href="<?= url('/manager/doctors') ?>">Отмена</a>
    </div>
</form>
