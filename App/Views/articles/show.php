<!-- //заглушка -->
<h1>Статья: <?= htmlspecialchars($article['title']) ?></h1>
<p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
<a href="/">← Назад</a>