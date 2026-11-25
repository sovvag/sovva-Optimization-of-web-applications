<?php
// Страница расписания: таблица "врачи x время (строки по 10 минут)".
// Переменные: $date, $prevDate, $nextDate, $grid, $dayTotal, $pendingTotal, $canApprove, $canManage
$isToday   = ($date === date('Y-m-d'));
$durations = range(10, 180, 10);
$reasons   = reason_options();
?>
<div class="page-head-row">
    <div>
        <h1>Расписание</h1>
        <p class="muted"><?= e(date_ru($date)) ?><?= $isToday ? ' (сегодня)' : '' ?></p>
    </div>

    <div class="date-nav">
<?php if ($canManage): ?>
        <button type="button" class="btn btn-primary" id="create-open">Запись</button>
<?php endif; ?>
        <a class="btn btn-ghost btn-icon" href="<?= url('/schedule?date=' . $prevDate) ?>" aria-label="Предыдущий день">&lsaquo;</a>
        <form method="get" action="<?= url('/schedule') ?>" id="date-form">
            <input type="date" name="date" id="date-input" value="<?= e($date) ?>" aria-label="Выбрать дату">
        </form>
        <a class="btn btn-ghost btn-icon" href="<?= url('/schedule?date=' . $nextDate) ?>" aria-label="Следующий день">&rsaquo;</a>
        <a class="btn btn-ghost" href="<?= url('/schedule') ?>">Сегодня</a>
    </div>
</div>

<!-- Эту область JavaScript обновляет после каждого действия без перезагрузки страницы -->
<div id="sched-area">
    <div class="sched-bar">
        <span class="chip-stat<?= $pendingTotal > 0 ? ' chip-warn' : '' ?>">Ожидают подтверждения: <strong><?= (int)$pendingTotal ?></strong></span>
        <span class="chip-stat">Записей на этот день: <strong><?= (int)$dayTotal ?></strong></span>
        <span class="legend">
            <span><i class="sw sw-free"></i>Свободно</span>
            <span><i class="sw sw-pending"></i>Ожидает</span>
            <span><i class="sw sw-booked"></i>Подтверждена</span>
            <span><i class="sw sw-done"></i>Завершена</span>
            <span><i class="sw sw-off"></i>Не работает</span>
        </span>
    </div>

<?php if ($grid === null): ?>
    <div class="panel empty">
        <p>На этот день расписание врачей не составлено.</p>
        <p class="muted small">Попробуйте другую дату. Смены врачей добавляет руководитель на странице «График».</p>
    </div>
<?php else: ?>
    <div class="sched-wrap">
        <table class="sched">
            <thead>
                <tr>
                    <th class="time-col"></th>
<?php foreach ($grid['columns'] as $col): ?>
                    <th class="doc-col">
                        <span class="doc-name"><?= e($col['name']) ?></span>
                        <span class="doc-spec"><?= e($col['specialty']) ?></span>
                        <span class="doc-load"><?= (int)$col['busy'] ?> из <?= (int)$col['total'] ?> занято</span>
                    </th>
<?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
<?php foreach ($grid['rows'] as $row): ?>
                <tr class="row-<?= e($row['kind']) ?>">
                    <th class="time-col"><?= e($row['label']) ?></th>
<?php foreach ($row['cells'] as $ci => $cell): ?>
<?php
    if ($cell === 'skip') {
        continue;                                   // ячейку занимает блок из строки выше
    }
    if ($cell === null) {                           // врач не работает
        echo '<td class="cell cell-off"></td>';
        continue;
    }
    $span = (int)$cell['span'];
    $col  = $grid['columns'][$ci];

    if ($cell['type'] === 'free') {                 // свободное время
        if ($canManage) {
            $minutes = min($span * 10, $col['minutes']);
            echo '<td class="cell cell-free" rowspan="' . $span . '"><button type="button" class="free-btn"'
               . ' data-doctor="' . (int)$col['id'] . '" data-time="' . e(minutes_to_time($cell['start'])) . '"'
               . ' data-minutes="' . (int)$minutes . '" aria-label="Записать на ' . e(minutes_to_time($cell['start'])) . '"></button></td>';
        } else {
            echo '<td class="cell cell-free" rowspan="' . $span . '"></td>';
        }
        continue;
    }

    // Запись пациента: цвет зависит от статуса
    $classes = ['pending' => 'cell-pending', 'confirmed' => 'cell-booked', 'completed' => 'cell-done'];
    $cls   = isset($classes[$cell['status']]) ? $classes[$cell['status']] : 'cell-busy';
    $start = minutes_to_time($cell['start_min']);
    $end   = minutes_to_time($cell['start_min'] + (int)$cell['duration']);
?>
                    <td class="cell <?= $cls ?><?= $span <= 2 ? ' cell-short' : '' ?>" rowspan="<?= $span ?>">
                        <button type="button" class="cell-btn"
                            data-id="<?= (int)$cell['id'] ?>"
                            data-status="<?= e($cell['status']) ?>"
                            data-status-label="<?= e(appointment_status_label($cell['status'])) ?>"
                            data-patient="<?= e(full_name($cell)) ?>"
                            data-patient-url="<?= e(url('/patients/' . (int)$cell['patient_id'] . '/edit')) ?>"
                            data-phone="<?= e($cell['phone'] ? $cell['phone'] : 'не указан') ?>"
                            data-doctor="<?= e($col['name']) ?>"
                            data-start="<?= e($start) ?>"
                            data-end="<?= e($end) ?>"
                            data-duration="<?= (int)$cell['duration'] ?>"
                            data-reason="<?= e($cell['reason']) ?>"
                            data-note="<?= e($cell['note']) ?>"
                            data-price="<?= e(number_format((float)$cell['price'], 0, ',', ' ') . ' ₽') ?>">
                            <strong><?= e(short_name($cell)) ?></strong>
                            <small><?= e($start . '–' . $end) ?> &middot; <?= e(reason_label($cell['reason'])) ?><?= $cell['status'] === 'pending' ? ' &middot; заявка' : '' ?></small>
                        </button>
                    </td>
<?php endforeach; ?>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>

<!-- ===== Окно записи: детали, перенос, повод, комментарий ===== -->
<div class="modal" id="slot-modal" hidden
     data-url="<?= e(url('/schedule/appointments')) ?>"
     data-manage="<?= $canManage ? '1' : '0' ?>" data-approve="<?= $canApprove ? '1' : '0' ?>">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-card modal-md" role="dialog" aria-modal="true" aria-labelledby="sm-title">
        <div class="modal-head">
            <h2 id="sm-title">Запись на приём</h2>
            <button type="button" class="modal-close" data-close aria-label="Закрыть">&times;</button>
        </div>

        <dl class="detail">
            <dt>Пациент</dt><dd><a id="sm-patient" href="#"></a></dd>
            <dt>Телефон</dt><dd id="sm-phone"></dd>
            <dt>Врач</dt><dd id="sm-doctor"></dd>
            <dt>Стоимость</dt><dd id="sm-price"></dd>
            <dt>Статус</dt><dd><span class="status" id="sm-status"></span></dd>
        </dl>

        <div class="time-row">
            <button type="button" class="arrow" id="sm-earlier" aria-label="Раньше на 10 минут">&lsaquo;</button>
            <div class="time-box"><strong id="sm-time"></strong><span class="muted small">шаг 10 минут</span></div>
            <button type="button" class="arrow" id="sm-later" aria-label="Позже на 10 минут">&rsaquo;</button>
        </div>

        <div class="form-grid modal-form">
            <label>Длительность приёма
                <select id="sm-duration">
<?php foreach ($durations as $d): ?>
                    <option value="<?= $d ?>"><?= $d ?> мин</option>
<?php endforeach; ?>
                </select>
            </label>
            <label>Повод обращения
                <select id="sm-reason">
<?php foreach ($reasons as $code => $label): ?>
                    <option value="<?= e($code) ?>"><?= e($label) ?></option>
<?php endforeach; ?>
                </select>
            </label>
            <label class="form-full">Комментарий
                <textarea id="sm-note" rows="3" maxlength="500" placeholder="Например: жалобы, пожелания пациента"></textarea>
            </label>
        </div>

        <p class="booking-msg" id="sm-msg" hidden></p>

        <div class="modal-actions modal-actions-wrap">
<?php if ($canManage): ?>
            <button type="button" class="btn btn-primary" id="sm-save">Сохранить</button>
<?php endif; ?>
<?php if ($canApprove): ?>
            <button type="button" class="btn btn-primary" id="sm-approve" hidden>Одобрить</button>
            <button type="button" class="btn btn-ghost btn-danger" id="sm-reject" hidden>Отклонить</button>
<?php endif; ?>
<?php if ($canManage): ?>
            <button type="button" class="btn btn-ghost btn-danger push-right" id="sm-delete">Удалить запись</button>
<?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canManage): ?>
<!-- ===== Окно "Запись": администратор сам записывает пациента ===== -->
<div class="modal" id="create-modal" hidden
     data-url="<?= e(url('/schedule/appointments')) ?>" data-search-url="<?= e(url('/api/patients/search')) ?>"
     data-date="<?= e($date) ?>">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-card modal-md" role="dialog" aria-modal="true" aria-labelledby="cm-title">
        <div class="modal-head">
            <h2 id="cm-title">Запись пациента на <?= e(date_ru($date)) ?></h2>
            <button type="button" class="modal-close" data-close aria-label="Закрыть">&times;</button>
        </div>

<?php if ($grid === null): ?>
        <p>На этот день нет смен врачей. Выберите другой день в расписании.</p>
<?php else: ?>
        <div class="form-grid modal-form">
            <div class="form-full">
                <label for="cm-search">Пациент</label>
                <input type="search" id="cm-search" placeholder="Фамилия, имя или телефон" maxlength="60" autocomplete="off">
                <div class="search-results" id="cm-results" hidden></div>
                <p class="chosen" id="cm-chosen" hidden><span id="cm-chosen-name"></span><button type="button" id="cm-clear" aria-label="Выбрать другого">&times;</button></p>
                <a class="muted small" href="<?= url('/patients/create') ?>">Нет в картотеке? Добавить пациента</a>
            </div>
            <label>Врач
                <select id="cm-doctor">
<?php foreach ($grid['columns'] as $col): ?>
                    <option value="<?= (int)$col['id'] ?>" data-minutes="<?= (int)$col['minutes'] ?>"><?= e($col['name']) ?>, <?= e($col['specialty']) ?></option>
<?php endforeach; ?>
                </select>
            </label>
            <label>Время начала
                <input type="time" id="cm-time" step="600" required>
            </label>
            <label>Длительность
                <select id="cm-duration">
<?php foreach ($durations as $d): ?>
                    <option value="<?= $d ?>"><?= $d ?> мин</option>
<?php endforeach; ?>
                </select>
            </label>
            <label>Повод обращения
                <select id="cm-reason">
<?php foreach ($reasons as $code => $label): ?>
                    <option value="<?= e($code) ?>"><?= e($label) ?></option>
<?php endforeach; ?>
                </select>
            </label>
            <label class="form-full">Комментарий
                <textarea id="cm-note" rows="2" maxlength="500"></textarea>
            </label>
        </div>

        <p class="booking-msg" id="cm-msg" hidden></p>

        <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="cm-submit">Записать</button>
            <button type="button" class="btn btn-ghost" data-close>Отмена</button>
        </div>
<?php endif; ?>
    </div>
</div>
<?php endif; ?>
