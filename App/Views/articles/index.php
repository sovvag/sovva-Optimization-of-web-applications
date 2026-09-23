<h1>Все статьи</h1>

<?php if (empty($articles)): ?>
    <p>Статей нет.</p>
<?php else: ?>
    <?php foreach ($articles as $a): ?>
        <div>
            <h3><?= htmlspecialchars($a['title']) ?></h3>
            <p><a href="/article/<?= $a['id'] ?>">читать</a></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>