<?php
$imageUrl = absolute_url('/assets/' . $article['image']);
$pageUrl  = absolute_url('/articles/' . $article['slug']);

// Микроразметка Schema.org, тип Article (JSON-LD)
$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $pageUrl],
    'headline'         => $article['h1'],
    'description'      => $article['description'],
    'image'            => [$imageUrl],
    'datePublished'    => $article['published'],
    'dateModified'     => $article['modified'],
    'inLanguage'       => 'ru',
    'author'           => ['@type' => 'Organization', 'name' => APP_NAME, 'url' => absolute_url('/')],
    'publisher'        => ['@type' => 'Organization', 'name' => APP_NAME, 'url' => absolute_url('/')],
];
// Логотип клиники (необязательно): если файл public/assets/img/logo.png есть, добавим его
if (file_exists(BASE_PATH . '/public/assets/img/logo.png')) {
    $schema['publisher']['logo'] = ['@type' => 'ImageObject', 'url' => absolute_url('/assets/img/logo.png')];
}
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>

<nav class="breadcrumbs" aria-label="Навигация">
    <a href="<?= url('/') ?>">Главная</a> / <a href="<?= url('/articles') ?>">Статьи</a> / <?= e($article['title']) ?>
</nav>

<article class="article">
    <h1><?= e($article['h1']) ?></h1>
    <p class="article-meta">Опубликовано: <?= e(date('d.m.Y', strtotime($article['published']))) ?></p>

    <figure class="article-figure">
        <img src="<?= e(asset($article['image'])) ?>" width="1200" height="630"
             alt="<?= e($article['image_alt']) ?>">
    </figure>

<?php require APP_PATH . '/Views/articles/' . $article['slug'] . '.php'; ?>
</article>