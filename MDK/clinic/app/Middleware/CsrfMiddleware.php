<?php
// Проверка CSRF-токена у всех POST-запросов
class CsrfMiddleware
{
    public static function handle()
    {
        if (!Csrf::verify()) {
            if (is_api_request()) {
                json_exit(['success' => false, 'message' => 'Страница устарела. Обновите её и повторите.'], 403);
            }
            // Если файл больше post_max_size, PHP отбрасывает всю форму целиком
            $message = 'Страница устарела. Вернитесь назад, обновите страницу и повторите действие.';
            if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
                $message = 'Отправленные данные слишком большие. Выберите файл поменьше (до 2 МБ).';
            }
            http_response_code(403);
            View::render('errors/403', [
                'title'   => 'Сессия устарела',
                'message' => $message,
                'noindex' => true,
            ]);
            exit;
        }
    }
}
