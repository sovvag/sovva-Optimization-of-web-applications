<div class="page-head-row">
    <div>
        <h1>Мои записи</h1>
        <p class="muted">Запись подтверждает администратор клиники. Статус обновляется на этой странице.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/doctors') ?>">Записаться ещё</a>
</div>

<?php if (empty($appointments)): ?>
    <div class="panel empty">
        <p>У вас пока нет записей.</p>
        <a class="btn btn-primary" href="<?= url('/doctors') ?>">Выбрать врача</a>
    </div>
<?php else: ?>
    <div class="app-list">
<?php foreach ($appointments as $a): ?>
<?php
    // Отменить можно ожидающую или подтверждённую запись, время которой ещё не прошло
    $isFuture  = $a['slot_date'] > $today || ($a['slot_date'] === $today && $a['slot_time'] > $nowTime);
    $canCancel = $isFuture && ($a['status'] === 'pending' || $a['status'] === 'confirmed');
?>
        <article class="app-card">
            <div class="app-when">
                <strong><?= e(time_short($a['slot_time'])) ?></strong>
                <span><?= e(date_ru($a['slot_date'])) ?></span>
            </div>
            <div class="app-who">
                <a href="<?= url('/doctors/' . $a['slug']) ?>"><?= e(full_name($a)) ?></a>
                <span class="muted"><?= e($a['specialty']) ?> &middot; <?= number_format((float)$a['price'], 0, ',', ' ') ?> &#8381;</span>
            </div>
            <span class="status status-<?= e($a['status']) ?>"><?= e(appointment_status_label($a['status'])) ?></span>
<?php if ($canCancel): ?>
            <form method="post" action="<?= url('/my-appointments/' . (int)$a['id'] . '/cancel') ?>" data-confirm="Отменить эту запись?">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-ghost btn-small">Отменить</button>
            </form>
<?php endif; ?>
        </article>
<?php endforeach; ?>
    </div>
<?php endif; ?>
