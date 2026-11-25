<section class="page-head">
    <h1>Наши врачи</h1>
    <p class="muted">Выберите специалиста, чтобы посмотреть подробности и записаться на приём.</p>
</section>

<section class="doctors-board">
<?php foreach ($doctors as $d): ?>
    <a class="dcard" href="<?= url('/doctors/' . $d['slug']) ?>">
        <div class="dc-photo">
<?php if (!empty($d['photo_path'])): ?>
            <img src="<?= e(url($d['photo_path'])) ?>" alt="<?= e(full_name($d)) ?>" loading="lazy">
<?php else: ?>
            <div class="dc-ph-body"></div><div class="dc-ph-head"></div>
<?php endif; ?>
        </div>
        <div class="dc-info">
            <div class="dc-solid"></div>
            <div class="dc-glass"></div>
            <div class="dc-text">
                <h2 class="dc-name"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></h2>
                <p class="dc-desc"><?= e($d['specialty']) ?>. <?= e(category_label($d['patient_category'])) ?></p>
                <div class="dc-row">
                    <span class="dc-stat"><?= e(years_text($d['experience_years'])) ?></span>
                    <span class="dc-btn">Подробнее</span>
                </div>
            </div>
        </div>
    </a>
<?php endforeach; ?>
<?php if (empty($doctors)): ?>
    <p class="muted">Врачи пока не добавлены.</p>
<?php endif; ?>
</section>
