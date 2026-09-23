<h1>Материалы</h1>
<table border="1" cellpadding="5">
	<tr><th>Код</th><th>Название</th><th>Цена за грамм (руб.)</th></tr>
	<?php foreach ($materials as $m): ?>
		<tr>
			<td><?= $m['id'] ?></td>
			<td><?= htmlspecialchars($m['name']) ?></td>
			<td><?= $m['price_per_gram'] ?></td>
		</tr>
	<?php endforeach; ?>
</table>