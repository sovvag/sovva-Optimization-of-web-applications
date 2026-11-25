<div class="page-head-row">
    <div>
        <h1>Картотека пациентов</h1>
        <p class="muted">Все пациенты клиники: <?= count($patients) ?></p>
    </div>
    <a class="btn btn-primary" href="<?= url('/patients/create') ?>">Добавить</a>
</div>

<form class="search-bar" method="get" action="<?= url('/patients') ?>">
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Поиск по ФИО или телефону" maxlength="60">
    <button class="btn btn-primary" type="submit">Найти</button>
<?php if ($query !== ''): ?>
    <a class="btn btn-ghost" href="<?= url('/patients') ?>">Сбросить</a>
<?php endif; ?>
</form>

<?php if (empty($patients)): ?>
    <div class="panel empty"><p>Пациенты не найдены.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Дата рождения</th>
                    <th>Телефон</th>
                    <th>Аккаунт на сайте</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($patients as $p): ?>
                <tr>
                    <td><strong><?= e(full_name($p)) ?></strong></td>
                    <td><?= $p['birth_date'] ? e(date('d.m.Y', strtotime($p['birth_date']))) : '&mdash;' ?></td>
                    <td><?= $p['phone'] ? e($p['phone']) : '&mdash;' ?></td>
                    <td><?= $p['email'] ? e($p['email']) : '<span class="muted">Без аккаунта</span>' ?></td>
                    <td class="col-actions">
                        <a class="btn btn-ghost btn-small" href="<?= url('/patients/' . (int)$p['id'] . '/edit') ?>">Изменить</a>
                        <button type="button" class="btn btn-ghost btn-small btn-danger js-delete"
                                data-id="<?= (int)$p['id'] ?>" data-name="<?= e(full_name($p)) ?>">Удалить</button>
                    </td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Окно: пациент подписал отказ? -->
<div class="modal" id="delete-modal" hidden data-base-url="<?= e(url('/patients')) ?>">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-card modal-sm" role="dialog" aria-modal="true" aria-labelledby="dm-title">
        <div class="modal-head">
            <h2 id="dm-title">Удаление карты пациента</h2>
            <button type="button" class="modal-close" data-close aria-label="Закрыть">&times;</button>
        </div>

        <div id="dm-question">
            <p>Пациент <strong id="dm-name"></strong></p>
            <p class="dm-ask">Пациент подписал отказ от предоставления персональных данных?</p>
            <div class="modal-actions">
                <form method="post" id="dm-form">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="refusal_signed" value="1">
                    <button type="submit" class="btn btn-primary">Да</button>
                </form>
                <button type="button" class="btn btn-ghost" id="dm-no">Нет</button>
            </div>
        </div>

        <div id="dm-denied" hidden>
            <p class="dm-ask">Без подписанного отказа удалить карту нельзя.</p>
            <p class="muted small">Попросите пациента подписать отказ и повторите удаление.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" data-close>Понятно</button>
            </div>
        </div>
    </div>
</div>
