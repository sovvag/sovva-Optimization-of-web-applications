<?php
$days = [1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'];
$months = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
$monthTitle = $months[(int)substr($month, 5, 2)] . ' ' . substr($month, 0, 4);
$docId = $doctor ? (int)$doctor['id'] : 0;
$durations = range(10, 180, 10);
?>
<div class="page-head-row">
    <div>
        <h1>График работы</h1>
        <p class="muted">Смены врачей. Для каждой смены автоматически создаются ячейки для записи.</p>
    </div>
</div>

<?php if (empty($doctors)): ?>
    <div class="panel empty"><p>Нет работающих врачей. Сначала добавьте врача.</p></div>
<?php else: ?>

<div class="sched-layout">
    <section class="panel">
        <div class="month-bar">
            <form method="get" action="<?= url('/manager/work-schedule') ?>" id="doctor-form" class="doctor-pick">
                <select name="doctor" id="doctor-select" aria-label="Врач">
<?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>"<?= $docId === (int)$d['id'] ? ' selected' : '' ?>><?= e(short_name($d)) ?> (<?= e($d['specialty']) ?>)</option>
<?php endforeach; ?>
                </select>
                <input type="hidden" name="month" value="<?= e($month) ?>">
            </form>
            <div class="month-nav">
                <a class="btn btn-ghost btn-icon" href="<?= url('/manager/work-schedule?doctor=' . $docId . '&month=' . $prevMonth) ?>" aria-label="Предыдущий месяц">&lsaquo;</a>
                <strong><?= e($monthTitle) ?></strong>
                <a class="btn btn-ghost btn-icon" href="<?= url('/manager/work-schedule?doctor=' . $docId . '&month=' . $nextMonth) ?>" aria-label="Следующий месяц">&rsaquo;</a>
            </div>
        </div>

<?php if (empty($shifts)): ?>
        <p class="muted">В этом месяце смен нет.</p>
<?php else: ?>
        <div class="table-wrap flat">
            <table class="table">
                <thead><tr><th>Дата</th><th>Время</th><th>Приём</th><th>Записей</th><th class="col-actions">Действия</th></tr></thead>
                <tbody>
<?php foreach ($shifts as $sh): ?>
                    <tr>
                        <td><strong><?= e(date_ru($sh['work_date'])) ?></strong></td>
                        <td><?= e(time_short($sh['start_time'])) ?> &ndash; <?= e(time_short($sh['end_time'])) ?></td>
                        <td><?= (int)$sh['slot_minutes'] ?> мин</td>
                        <td><?= (int)$sh['apps'] ?></td>
                        <td class="col-actions">
                            <button type="button" class="btn btn-ghost btn-small js-edit"
                                data-id="<?= (int)$sh['id'] ?>" data-date="<?= e(date_ru($sh['work_date'])) ?>"
                                data-start="<?= e(time_short($sh['start_time'])) ?>" data-end="<?= e(time_short($sh['end_time'])) ?>"
                                data-minutes="<?= (int)$sh['slot_minutes'] ?>" data-apps="<?= (int)$sh['apps'] ?>">Изменить</button>
                            <form method="post" action="<?= url('/manager/work-schedule/' . (int)$sh['id'] . '/delete') ?>" data-confirm="Удалить смену?">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-ghost btn-small btn-danger"<?= (int)$sh['apps'] > 0 ? ' disabled title="На этот день есть записи"' : '' ?>>Удалить</button>
                            </form>
                        </td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php endif; ?>
    </section>

    <section class="panel">
        <h2>Добавить смены</h2>
        <form method="post" action="<?= url('/manager/work-schedule') ?>" class="form-grid one-col">
            <?= Csrf::field() ?>
            <input type="hidden" name="doctor_id" value="<?= $docId ?>">
            <div class="two-cols">
                <label>С даты <input type="date" name="date_from" min="<?= date('Y-m-d') ?>" value="<?= e(max(date('Y-m-d'), $month . '-01')) ?>" required></label>
                <label>По дату <input type="date" name="date_to" min="<?= date('Y-m-d') ?>" value="<?= e(max(date('Y-m-d'), date('Y-m-t', strtotime($month . '-01')))) ?>" required></label>
            </div>
            <div class="weekdays" role="group" aria-label="Дни недели">
<?php foreach ($days as $n => $label): ?>
                <label class="wd"><input type="checkbox" name="weekdays[]" value="<?= $n ?>"<?= $n <= 5 ? ' checked' : '' ?>><span><?= $label ?></span></label>
<?php endforeach; ?>
            </div>
            <div class="two-cols">
                <label>Начало <input type="time" name="start" step="600" value="09:00" required></label>
                <label>Конец <input type="time" name="end" step="600" value="17:00" required></label>
            </div>
            <label>Длительность приёма
                <select name="minutes">
<?php foreach ($durations as $m): ?>
                    <option value="<?= $m ?>"<?= $m === 30 ? ' selected' : '' ?>><?= $m ?> мин</option>
<?php endforeach; ?>
                </select>
            </label>
            <p class="muted small">Дни, на которые у врача уже есть смена, пропускаются.</p>
            <button class="btn btn-primary" type="submit">Добавить смены</button>
        </form>
    </section>
</div>

<!-- Окно: изменить смену -->
<div class="modal" id="edit-modal" hidden data-base-url="<?= e(url('/manager/work-schedule')) ?>">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-card modal-sm" role="dialog" aria-modal="true" aria-labelledby="em-title">
        <div class="modal-head">
            <h2 id="em-title">Смена</h2>
            <button type="button" class="modal-close" data-close aria-label="Закрыть">&times;</button>
        </div>
        <p id="em-date" class="muted"></p>
        <p id="em-warn" class="booking-msg is-error" hidden>На этот день уже есть записи. Изменить смену нельзя.</p>
        <form method="post" id="em-form" class="form-grid one-col">
            <?= Csrf::field() ?>
            <div class="two-cols">
                <label>Начало <input type="time" name="start" id="em-start" step="600" required></label>
                <label>Конец <input type="time" name="end" id="em-end" step="600" required></label>
            </div>
            <label>Длительность приёма
                <select name="minutes" id="em-minutes">
<?php foreach ($durations as $m): ?>
                    <option value="<?= $m ?>"><?= $m ?> мин</option>
<?php endforeach; ?>
                </select>
            </label>
            <div class="modal-actions">
                <button class="btn btn-primary" type="submit" id="em-save">Сохранить</button>
                <button class="btn btn-ghost" type="button" data-close>Отмена</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
