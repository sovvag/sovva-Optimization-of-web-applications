<div class="page-head-row">
    <div>
        <h1>Врачи</h1>
        <p class="muted">Страница каждого врача на сайте создаётся автоматически.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/manager/doctors/create') ?>">Добавить врача</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>ФИО</th><th>Должность</th><th>Стаж</th><th>Приём</th><th>Стоимость</th><th>Статус</th><th class="col-actions">Действия</th></tr>
        </thead>
        <tbody>
<?php foreach ($doctors as $d): ?>
            <tr>
                <td><strong><?= e(full_name($d)) ?></strong></td>
                <td><?= e($d['specialty']) ?></td>
                <td><?= e(years_text($d['experience_years'])) ?></td>
                <td><?= e(category_label($d['patient_category'])) ?></td>
                <td><?= number_format((float)$d['price'], 0, ',', ' ') ?> &#8381;</td>
                <td>
<?php if ((int)$d['is_active'] === 1): ?>
                    <span class="status status-confirmed">Работает</span>
<?php else: ?>
                    <span class="status status-cancelled">Не работает</span>
<?php endif; ?>
                </td>
                <td class="col-actions">
<?php if ((int)$d['is_active'] === 1): ?>
                    <a class="btn btn-ghost btn-small" href="<?= url('/doctors/' . $d['slug']) ?>">На сайте</a>
<?php endif; ?>
                    <a class="btn btn-ghost btn-small" href="<?= url('/manager/doctors/' . (int)$d['id'] . '/edit') ?>">Изменить</a>
                </td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
