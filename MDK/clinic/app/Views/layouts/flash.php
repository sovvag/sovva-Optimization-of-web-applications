<?php foreach (get_flash() as $msg): ?>
    <div class="alert alert-<?= e($msg['type']) ?>" role="alert"><?= e($msg['message']) ?></div>
<?php endforeach; ?>
