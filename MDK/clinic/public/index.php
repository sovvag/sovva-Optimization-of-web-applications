<?php
// ============================================================
// index.php: единая точка входа. Все запросы идут через этот файл.
// ============================================================

require __DIR__ . '/../config/config.php';
require APP_PATH . '/Helpers/functions.php';

// --- Автозагрузка классов: ищем класс в папках app/... ---
spl_autoload_register(function ($class) {
    $folders = ['Core', 'Controllers', 'Models', 'Middleware'];
    foreach ($folders as $folder) {
        $file = APP_PATH . '/' . $folder . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// --- Заголовки безопасности ---
header('X-Frame-Options: DENY');                       // запрет вставки сайта в iframe
header('X-Content-Type-Options: nosniff');             // браузер не угадывает тип файла
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; "
     . "img-src 'self' data: https://mc.yandex.ru https://mc.yandex.com; "
     . "style-src 'self' 'unsafe-inline'; "
     . "script-src 'self' https://mc.yandex.ru https://mc.yandex.com https://yastatic.net; "
     . "connect-src 'self' https://mc.yandex.ru https://mc.yandex.com wss://mc.yandex.ru; "
     . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'");

// --- Безопасные настройки сессии ---
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
ini_set('session.use_strict_mode', '1');               // сервер не принимает чужой ID сессии
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,                                // JS не видит cookie (защита от XSS)
    'samesite' => 'Lax',
]);
session_start();

// --- Любая необработанная ошибка попадает сюда ---
set_exception_handler(function ($ex) {
    error_log($ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine());
    http_response_code(500);
    if (APP_ENV === 'development') {
        echo '<pre style="padding:20px">' . e($ex->getMessage()) . "\n\n"
           . e($ex->getFile()) . ':' . $ex->getLine() . '</pre>';
    } else {
        echo 'Произошла ошибка. Попробуйте позже.';
    }
});

// --- Проверка времени бездействия ---
Auth::checkTimeout();

// --- Запуск маршрутизатора ---
$router = new Router();
require BASE_PATH . '/config/routes.php';
$router->dispatch();
