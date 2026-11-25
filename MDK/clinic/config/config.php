<?php
// ============================================================
// config.php: общие настройки проекта
// Подключается один раз в public/index.php
// ============================================================

// --- Пути ---
define('BASE_PATH', dirname(__DIR__));          // папка clinic
define('APP_PATH', BASE_PATH . '/app');         // папка clinic/app

// BASE_URL нужен, если сайт открыт не из корня (например /clinic/public)
// Если корень сайта = папка public, BASE_URL будет пустой строкой
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));

// --- Чтение файла .env ---
$ENV = [];
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;                           // пропускаем комментарии
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

// Получить значение из .env: env('DB_HOST', 'localhost')
function env($key, $default = null)
{
    global $ENV;
    return isset($ENV[$key]) ? $ENV[$key] : $default;
}

define('APP_NAME', env('APP_NAME', 'VisioClinic'));
define('APP_ENV', env('APP_ENV', 'production'));

// --- Настройки безопасности ---
define('SESSION_LIFETIME', 30 * 60);   // выход после 30 минут бездействия
define('LOGIN_MAX_ATTEMPTS', 5);       // сколько неудачных входов разрешено
define('LOGIN_BLOCK_MINUTES', 15);     // на сколько минут блокируем

// --- Показ ошибок ---
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php_errors.log');

date_default_timezone_set('Europe/Moscow');
