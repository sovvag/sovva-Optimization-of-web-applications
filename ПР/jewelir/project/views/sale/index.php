<h1>Продажи</h1>
<table border="1" cellpadding="5">
	<tr><th>Код продажи</th><th>Изделие</th><th>Дата</th><th>Покупатель</th></tr>
	<?php foreach ($sales as $s): ?>
		<tr>
			<td><?= $s['id'] ?></td>
			<td><?= htmlspecialchars($s['product_name']) ?></td>
			<td><?= $s['sale_date'] ?></td>
			<td><?= htmlspecialchars($s['buyer_name']) ?></td>
		</tr>
	<?php endforeach; ?>
</table>