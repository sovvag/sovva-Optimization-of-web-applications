<?php
// Проверка: есть ли у пользователя нужное право?
// Проверяем НА СЕРВЕРЕ. Скрытая кнопка в меню - это не защита.
class RoleMiddleware
{
    public static function handle($permission)
    {
        AuthMiddleware::handle();

        if (!Auth::can($permission)) {
            if (is_api_request()) {
                json_exit(['success' => false, 'message' => 'Недостаточно прав для этого действия.'], 403);
            }
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ запрещён', 'noindex' => true]);
            exit;
        }
    }
}
