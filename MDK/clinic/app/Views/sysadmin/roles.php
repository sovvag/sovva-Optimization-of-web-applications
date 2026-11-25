<div class="page-head-row">
    <div>
        <h1>Права ролей</h1>
        <p class="muted">Отметьте, что может делать каждая роль. Изменения действуют сразу.</p>
    </div>
</div>

<form method="post" action="<?= url('/sysadmin/roles') ?>">
    <?= Csrf::field() ?>

    <div class="table-wrap">
        <table class="table matrix">
            <thead>
                <tr>
                    <th>Право</th>
<?php foreach ($roles as $r): ?>
                    <th class="mx-col"><?= e($r['title']) ?></th>
<?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
<?php foreach ($permissions as $p): ?>
<?php $isSystem = in_array($p['code'], $systemOnly, true); ?>
                <tr>
                    <td>
                        <strong><?= e($p['description']) ?></strong><br>
                        <code><?= e($p['code']) ?></code>
                    </td>
<?php foreach ($roles as $r): ?>
<?php
    $isSysadmin = ($r['name'] === 'sysadmin');
    $checked = isset($granted[$r['id'] . '-' . $p['id']]);
    $forced = $isSysadmin && in_array($p['code'], $locked, true);     // у сисадмина обязательно
    $forbidden = $isSystem && !$isSysadmin;                            // системное право только сисадмину
?>
                    <td class="mx-col">
<?php if ($forced): ?>
                        <input type="hidden" name="perm[<?= (int)$r['id'] ?>][]" value="<?= (int)$p['id'] ?>">
                        <input type="checkbox" checked disabled title="Это право нельзя забрать у системного администратора">
<?php elseif ($forbidden): ?>
                        <span class="muted" title="Это право есть только у системного администратора">&mdash;</span>
<?php else: ?>
                        <label class="mx"><input type="checkbox" name="perm[<?= (int)$r['id'] ?>][]" value="<?= (int)$p['id'] ?>"<?= $checked ? ' checked' : '' ?>><span></span></label>
<?php endif; ?>
                    </td>
<?php endforeach; ?>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Сохранить права</button>
    </div>
</form>

<div class="panel note-panel">
    <h2>Правила безопасности</h2>
    <ul>
        <li>Права <code>roles.manage</code> и <code>users.manage</code> есть только у системного администратора. Другим ролям выдать их нельзя: иначе та роль смогла бы сама расширить свои права.</li>
        <li>У системного администратора нельзя снять управление правами и аккаунтами: так нельзя случайно потерять доступ к панели.</li>
        <li>Проверка прав выполняется на сервере при каждом запросе. Если снять право, пункт меню пропадёт, а прямой адрес страницы вернёт ошибку 403.</li>
    </ul>
</div>
