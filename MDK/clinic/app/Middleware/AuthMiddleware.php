<?php
// Проверка: пользователь вошёл в аккаунт?
class AuthMiddleware
{
    public static function handle()
    {
        if (!Auth::check()) {
            if (is_api_request()) {
                json_exit(['success' => false, 'message' => 'Войдите в аккаунт и повторите попытку.'], 401);
            }
            flash('error', 'Для этой страницы нужно войти в аккаунт.');
            redirect('/login');
        }
    }
}
