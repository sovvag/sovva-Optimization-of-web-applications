<?php
// ============================================================
// Controller.php: базовый класс для всех контроллеров.
// ============================================================
class Controller
{
    // Показать страницу
    protected function view($name, $data = [], $layout = 'main')
    {
        View::render($name, $data, $layout);
    }

    // Страница не найдена (404)
    protected function notFound()
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Страница не найдена', 'noindex' => true]);
        exit;
    }

    // Ответ в формате JSON (для AJAX-запросов)
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
