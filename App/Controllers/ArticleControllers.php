<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Article;

class ArticleControllers extends Controller
{
    private Article $model;

    public function __construct()
    {
        $this->model = new Article();
    }

    public function index(): void{
        $this->view('articles/index', ['articles' => $this->model->all()]);
    }

    public function show(array $params): void{
    $id = $params['id'] ?? $params[0] ?? null;

    if ($id === null) {
        http_response_code(400);
        exit("Не передан ID");
    }

    $article = $this->model->find((int)$id);

    if (!$article) {
        http_response_code(404);
        exit("Статья не найдена");
    }

    $this->view('articles/show', ['article' => $article]);
    }

}