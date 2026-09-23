<h1>Ювелирные изделия</h1>
<table border="1" cellpadding="5">
	<tr>
		<th>Код</th><th>Название</th><th>Тип</th>
		<th>Материал</th><th>Вес (г)</th><th>Цена (руб.)</th><th></th>
	</tr>
	<?php foreach ($products as $p): ?>
		<tr>
			<td><?= $p['id'] ?></td>
			<td><?= htmlspecialchars($p['name']) ?></td>
			<td><?= htmlspecialchars($p['type']) ?></td>
			<td><?= htmlspecialchars($p['material_name']) ?></td>
			<td><?= $p['weight'] ?></td>
			<td><?= $p['price'] ?></td>
			<td><a href="/products/show/<?= $p['id'] ?>/">Подробнее</a></td>
		</tr>
	<?php endforeach; ?>
</table>