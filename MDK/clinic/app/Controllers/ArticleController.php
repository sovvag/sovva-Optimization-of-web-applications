<?php
// Блог: список статей и страница одной статьи
class ArticleController extends Controller
{
    // GET /articles
    public function index()
    {
        $this->view('articles/index', [
            'title'       => 'Статьи о зрении и лечении глаз',
            'description' => 'Полезные статьи офтальмологов: симптомы заболеваний глаз, диагностика и лечение.',
            'articles'    => require BASE_PATH . '/config/articles.php',
        ]);
    }

    // GET /articles/{slug}
    public function show($slug)
    {
        $articles = require BASE_PATH . '/config/articles.php';
        if (!isset($articles[$slug])) {
            $this->notFound();
        }
        $article = $articles[$slug];
        $article['slug'] = $slug;

        $this->view('articles/show', [
            'title'       => $article['title'],
            'description' => $article['description'],
            'canonical'   => absolute_url('/articles/' . $slug),
            'ogImage'     => absolute_url('/assets/' . $article['image']),
            'article'     => $article,
            'styles'      => [],
        ]);
    }
}