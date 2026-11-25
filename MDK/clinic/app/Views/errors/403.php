<section class="center-box">
    <h1>403</h1>
    <p><?= e(isset($message) ? $message : 'У вас нет доступа к этой странице.') ?></p>
    <a class="btn btn-primary" href="<?= url('/') ?>">На главную</a>
</section>
