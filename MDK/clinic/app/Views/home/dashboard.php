<section class="panel">
    <h1>Здравствуйте, <?= e(Auth::user()['first_name']) ?>!</h1>
    <p>Ваша роль: <strong><?= e(Auth::user()['role_title']) ?></strong></p>
    <p class="muted">Рабочие страницы вашей роли появятся на следующих шагах разработки.
       Ниже права, которые сейчас даны вашей роли (берутся из базы данных):</p>
    <ul class="perm-list">
<?php foreach ($permissions as $code): ?>
        <li><code><?= e($code) ?></code></li>
<?php endforeach; ?>
    </ul>
</section>
