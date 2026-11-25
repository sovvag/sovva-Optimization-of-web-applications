<?php
// ============================================================
// Database.php: подключение к MySQL через PDO.
// Подключение создаётся один раз и используется везде.
// ============================================================
class Database
{
    private static $pdo = null;

    public static function get()
    {
        if (self::$pdo === null) {
            $cfg = require BASE_PATH . '/config/database.php';

            $dsn = 'mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['name']
                 . ';charset=' . $cfg['charset'];

            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // ошибки как исключения
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // строки как массивы
                PDO::ATTR_EMULATE_PREPARES   => false,                    // настоящие подготовленные запросы
            ]);
        }
        return self::$pdo;
    }
}
