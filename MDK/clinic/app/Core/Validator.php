<?php
// ============================================================
// Validator.php: проверка данных из форм НА СЕРВЕРЕ.
// Проверка в JavaScript нужна только для удобства: её легко обойти.
// ============================================================
class Validator
{
    public static function email($value)
    {
        return strlen($value) <= 150 && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Фамилия, имя, отчество: только буквы, пробел и дефис
    public static function name($value)
    {
        return (bool)preg_match('/^[\p{L}][\p{L} \-]{0,59}$/u', $value);
    }

    // Возвращает текст ошибки или пустую строку, если пароль подходит
    public static function password($value)
    {
        if (strlen($value) < 8 || strlen($value) > 64) {
            return 'Пароль должен быть от 8 до 64 символов';
        }
        if (!preg_match('/[a-zа-я]/u', $value) || !preg_match('/[A-ZА-Я]/u', $value) || !preg_match('/[0-9]/', $value)) {
            return 'Нужна строчная и заглавная буква и цифра';
        }
        return '';
    }

    // Дата рождения в формате ГГГГ-ММ-ДД, не из будущего
    public static function birthDate($value)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $parts = explode('-', $value);
        if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            return false;
        }
        return $value <= date('Y-m-d') && $value >= '1900-01-01';
    }

    // Любая корректная дата в формате ГГГГ-ММ-ДД
    public static function dateValue($value)
    {
        if (!is_string($value) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            return false;
        }
        return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
    }

    // Телефон: цифры, пробелы, скобки, дефис, плюс в начале (6-20 символов)
    public static function phone($value)
    {
        return (bool)preg_match('/^\+?[0-9\s\-()]{6,20}$/', $value);
    }
}
