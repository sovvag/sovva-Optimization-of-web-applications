<section class="page-head">
    <h1>Статьи о зрении</h1>
    <p class="muted">Полезные материалы наших врачей.</p>
</section>
<div class="steps-grid">
<?php foreach ($articles as $slug => $a): ?>
    <article class="step">
        <h2><a href="<?= url('/articles/' . $slug) ?>"><?= e($a['h1']) ?></a></h2>
        <p><?= e($a['excerpt']) ?></p>
        <p class="muted small"><?= e(date('d.m.Y', strtotime($a['published']))) ?></p>
    </article>
<?php endforeach; ?>
</div>
