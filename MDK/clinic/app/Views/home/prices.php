<section class="page-head">
    <h1>Цены на приём</h1>
    <p class="muted">Стоимость первичного приёма у каждого врача.</p>
</section>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Врач</th><th>Должность</th><th>Стоимость</th><th></th></tr></thead>
        <tbody>
<?php foreach ($doctors as $d): ?>
            <tr>
                <td><strong><?= e(full_name($d)) ?></strong></td>
                <td><?= e($d['specialty']) ?></td>
                <td><?= number_format((float)$d['price'], 0, ',', ' ') ?> &#8381;</td>
                <td class="col-actions"><a class="btn btn-primary btn-small" href="<?= url('/doctors/' . $d['slug']) ?>">Записаться</a></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>