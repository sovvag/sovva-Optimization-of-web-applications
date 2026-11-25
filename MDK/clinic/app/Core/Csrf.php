<?php
// ============================================================
// Csrf.php: защита от подделки запросов (CSRF).
// В каждую форму вставляем секретный токен, на сервере сверяем.
// Чужой сайт этот токен знать не может.
// ============================================================
class Csrf
{
    public static function token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Скрытое поле для формы (в шаблоне вызывается как Csrf::field())
    public static function field()
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    // Проверка токена (из формы или из заголовка AJAX-запроса)
    public static function verify()
    {
        $sent = '';
        if (isset($_POST['csrf_token'])) {
            $sent = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $sent = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        return is_string($sent) && hash_equals(self::token(), $sent);
    }
}
