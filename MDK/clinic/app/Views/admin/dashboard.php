<div class="admin-page-head"><div><p class="admin-eyebrow">VISIOCLINIC / АДМИНИСТРАТОР</p><h1>Добро пожаловать, <?= e(Auth::user()["first_name"]) ?>!</h1><p class="muted">Сводка по заявкам и работе клиники</p></div><a class="btn btn-primary" href="<?= url("/admin/schedule") ?>">Открыть расписание</a></div>
<div class="admin-stats">
 <article class="admin-stat"><span>Новые заявки</span><strong><?= (int)$stats["pending"] ?></strong><small>Ожидают подтверждения</small></article>
 <article class="admin-stat"><span>Записи на сегодня</span><strong><?= (int)$stats["today"] ?></strong><small>Ожидают приёма или подтверждения</small></article>
 <article class="admin-stat"><span>Пациенты</span><strong><?= (int)$stats["patients"] ?></strong><small>Карточек в базе</small></article>
</div>
<section class="admin-section"><div class="admin-section-head"><div><h2>Новые заявки</h2><p class="muted">Заявки, которым требуется решение администратора</p></div><a href="<?= url("/admin/schedule?status=pending") ?>">Все заявки →</a></div>
<?php if (!$recent): ?><div class="admin-empty">Новых заявок пока нет.</div>
<?php else: ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Пациент</th><th>Врач</th><th>Дата и время</th><th>Стоимость</th><th></th></tr></thead><tbody>
<?php foreach (array_slice($recent,0,6) as $a): ?><tr><td><?= e(trim($a["patient_last_name"]." ".$a["patient_first_name"]." ".$a["patient_middle_name"])) ?></td><td><?= e(trim($a["doctor_last_name"]." ".$a["doctor_first_name"])) ?></td><td><?= date("d.m.Y",strtotime($a["slot_date"])) ?>, <?= e(substr($a["slot_time"],0,5)) ?></td><td><?= number_format((float)$a["price"],0,","," ") ?> ₽</td><td><a class="admin-text-link" href="<?= url("/admin/schedule?status=pending") ?>">Обработать</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
