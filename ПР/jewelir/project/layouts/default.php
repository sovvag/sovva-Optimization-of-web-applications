<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= htmlspecialchars($title ?? 'Ювелирный магазин') ?></title>
	<link rel="stylesheet" href="/css/style.css">
</head>
<body>
	<header class="header">
		<div class="container header__inner">
			<a class="logo" href="/products/">Ювелирка</a>
			<nav class="nav">
				<a href="/products/">Изделия</a>
				<a href="/materials/">Материалы</a>
				<a href="/sales/">Продажи</a>
			</nav>
		</div>
	</header>

	<main class="container">
		<?= $content ?>
	</main>

	<footer class="footer">
		<div class="container">© Ювелирный магазин</div>
	</footer>
</body>
</html>
