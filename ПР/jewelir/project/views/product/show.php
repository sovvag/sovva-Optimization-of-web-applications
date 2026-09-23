<h1><?= htmlspecialchars($product['name']) ?></h1>
<p><b>Код изделия:</b> <?= $product['id'] ?></p>
<p><b>Тип:</b> <?= htmlspecialchars($product['type']) ?></p>
<p><b>Материал:</b> <?= htmlspecialchars($product['material_name']) ?>
	(<?= $product['price_per_gram'] ?> руб./г)</p>
<p><b>Вес:</b> <?= $product['weight'] ?> г</p>
<p><b>Цена:</b> <?= $product['price'] ?> руб.</p>
<p><a href="/products/">← Ко всем изделиям</a></p>