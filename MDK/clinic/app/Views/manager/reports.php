<?php
$q = function ($extra = []) use ($tab, $period, $from, $to) {
    return http_build_query(array_merge(['tab' => $tab, 'period' => $period, 'from' => $from, 'to' => $to], $extra));
};
$tabs = ['hours' => 'Выработка часов', 'revenue' => 'Прибыль', 'actions' => 'Действия администраторов'];
$periods = ['day' => 'День', 'week' => 'Неделя', 'month' => 'Месяц', 'custom' => 'Свой период'];

// итоги
$tot = ['shifts' => 0, 'planned' => 0, 'booked' => 0, 'visits' => 0, 'revenue' => 0];
$maxRevenue = 0;
if ($tab !== 'actions') {
    foreach ($rows as $r) {
        $tot['shifts'] += $r['shifts'];
        $tot['planned'] += $r['planned'];
        $tot['booked'] += $r['booked'];
        $tot['visits'] += $r['visits'];
        $tot['revenue'] += $r['revenue'];
        $maxRevenue = max($maxRevenue, $r['revenue']);
    }
}
?>
<div class="page-head-row">
    <div>
        <h1>Отчёты</h1>
        <p class="muted">Период: <?= e(date('d.m.Y', strtotime($from))) ?> &ndash; <?= e(date('d.m.Y', strtotime($to))) ?></p>
    </div>
    <a class="btn btn-ghost" href="<?= url('/manager/reports?' . $q(['export' => 1])) ?>">Скачать CSV</a>
</div>

<div class="tabs">
<?php foreach ($tabs as $code => $label): ?>
    <a class="tab<?= $tab === $code ? ' active' : '' ?>" href="<?= url('/manager/reports?' . $q(['tab' => $code])) ?>"><?= e($label) ?></a>
<?php endforeach; ?>
</div>

<div class="period-bar">
<?php foreach ($periods as $code => $label): ?>
<?php if ($code !== 'custom'): ?>
    <a class="chip-btn<?= $period === $code ? ' active' : '' ?>" href="<?= url('/manager/reports?' . http_build_query(['tab' => $tab, 'period' => $code])) ?>"><?= e($label) ?></a>
<?php endif; ?>
<?php endforeach; ?>
    <form class="custom-period" method="get" action="<?= url('/manager/reports') ?>">
        <input type="hidden" name="tab" value="<?= e($tab) ?>">
        <input type="hidden" name="period" value="custom">
        <input type="date" name="from" value="<?= e($from) ?>" aria-label="С даты" required>
        <span class="muted">&ndash;</span>
        <input type="date" name="to" value="<?= e($to) ?>" aria-label="По дату" required>
        <button class="chip-btn<?= $period === 'custom' ? ' active' : '' ?>" type="submit">Показать</button>
    </form>
</div>

<?php if ($tab === 'actions'): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Администратор</th><th>Добавил записей</th><th>Удалил записей</th><th>Подтвердил</th><th>Отклонил</th><th>Перенёс</th></tr></thead>
            <tbody>
<?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e(full_name($r)) ?></strong></td>
                    <td><?= (int)$r['created'] ?></td><td><?= (int)$r['deleted'] ?></td>
                    <td><?= (int)$r['confirmed'] ?></td><td><?= (int)$r['rejected'] ?></td><td><?= (int)$r['moved'] ?></td>
                </tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?>
                <tr><td colspan="6" class="muted">Администраторов нет.</td></tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Данные берутся из журнала действий. Удаление записи фиксируется в журнале навсегда.</p>

<?php elseif ($tab === 'hours'): ?>
    <div class="stat-row">
        <div class="stat"><span>Смен</span><strong><?= (int)$tot['shifts'] ?></strong></div>
        <div class="stat"><span>Часов по графику</span><strong><?= round($tot['planned'], 1) ?></strong></div>
        <div class="stat"><span>Часов записано</span><strong><?= round($tot['booked'], 1) ?></strong></div>
        <div class="stat"><span>Загрузка</span><strong><?= $tot['planned'] > 0 ? round($tot['booked'] / $tot['planned'] * 100) : 0 ?>%</strong></div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Врач</th><th>Смен</th><th>Часов по графику</th><th>Часов записано</th><th>Загрузка</th><th>Приёмов</th></tr></thead>
            <tbody>
<?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><span class="muted small"><?= e($r['specialty']) ?></span></td>
                    <td><?= (int)$r['shifts'] ?></td>
                    <td><?= round($r['planned'], 1) ?></td>
                    <td><?= round($r['booked'], 1) ?></td>
                    <td>
                        <div class="bar"><i style="width:<?= min(100, (int)$r['load']) ?>%"></i></div>
                        <span class="small"><?= (int)$r['load'] ?>%</span>
                    </td>
                    <td><?= (int)$r['visits'] ?></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Часы записано: подтверждённые и завершённые приёмы.</p>

<?php else: ?>
    <div class="stat-row">
        <div class="stat"><span>Выручка</span><strong><?= number_format($tot['revenue'], 0, ',', ' ') ?> &#8381;</strong></div>
        <div class="stat"><span>Приёмов</span><strong><?= (int)$tot['visits'] ?></strong></div>
        <div class="stat"><span>Средний чек</span><strong><?= $tot['visits'] > 0 ? number_format($tot['revenue'] / $tot['visits'], 0, ',', ' ') : 0 ?> &#8381;</strong></div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Врач</th><th>Приёмов</th><th>Выручка</th><th class="bar-col">Доля</th></tr></thead>
            <tbody>
<?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><span class="muted small"><?= e($r['specialty']) ?></span></td>
                    <td><?= (int)$r['visits'] ?></td>
                    <td><?= number_format($r['revenue'], 0, ',', ' ') ?> &#8381;</td>
                    <td class="bar-col"><div class="bar"><i style="width:<?= $maxRevenue > 0 ? round($r['revenue'] / $maxRevenue * 100) : 0 ?>%"></i></div></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Выручка: стоимость подтверждённых и завершённых приёмов за период.</p>
<?php endif; ?>
