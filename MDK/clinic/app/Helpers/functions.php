<?php
// ============================================================
// functions.php: маленькие функции-помощники для всего проекта
// ============================================================

// Безопасный вывод текста на страницу (защита от XSS).
// ВСЕГДА используйте e() при выводе данных из базы или от пользователя.
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Адрес внутри сайта: url('/doctors') -> /doctors
function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// Адрес файла из public/assets: asset('css/main.css')
function asset($path)
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

// Полный адрес с доменом (нужен для SEO: canonical, sitemap)
function absolute_url($path = '')
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return $scheme . '://' . $host . url($path);
}

// Безопасно взять текстовое поле из формы.
// Если прислали не строку (например password[]=1), вернём пустую строку.
function post_str($key, $trim = true)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return '';
    }
    return $trim ? trim($_POST[$key]) : $_POST[$key];
}

// Перенаправление на другую страницу
function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

// --- Сообщения для пользователя (показываются один раз) ---
function flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
    unset($_SESSION['flash']);
    return $messages;
}

// Достать значение из сессии и сразу удалить (для ошибок формы)
function pull($key, $default = null)
{
    if (isset($_SESSION[$key])) {
        $value = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }
    return $default;
}

// --- Работа с ФИО ---
// "Иванов Алексей Петрович" -> "Иванов А. П."
function short_name($person)
{
    $result = $person['last_name'];
    if (!empty($person['first_name'])) {
        $result .= ' ' . mb_substr($person['first_name'], 0, 1) . '.';
    }
    if (!empty($person['middle_name'])) {
        $result .= ' ' . mb_substr($person['middle_name'], 0, 1) . '.';
    }
    return $result;
}

function full_name($person)
{
    $parts = [$person['last_name'], $person['first_name']];
    if (!empty($person['middle_name'])) {
        $parts[] = $person['middle_name'];
    }
    return implode(' ', $parts);
}

// Склонение: plural(12, 'год', 'года', 'лет') -> "лет"
function plural($n, $one, $two, $five)
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $five;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $two;
    }
    if ($n1 == 1) {
        return $one;
    }
    return $five;
}

// "12 лет", "1 год", "3 года"
function years_text($n)
{
    return $n . ' ' . plural($n, 'год', 'года', 'лет');
}

// Для кого принимает врач (значение из БД -> текст)
function category_label($category)
{
    $labels = [
        'adults'            => 'Только взрослые',
        'children'          => 'Только дети',
        'children14_adults' => 'Дети с 14 лет и взрослые',
    ];
    return isset($labels[$category]) ? $labels[$category] : '';
}

// --- Иконки (простые SVG-картинки, чтобы не подключать библиотеки) ---
function icon($name, $size = 20)
{
    $paths = [
        'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'key'   => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3M14 9l2 2"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'eye'   => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'user'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'cal'   => '<rect x="4" y="5" width="16" height="16" rx="3"/><path d="M8 3v4M16 3v4M4 10h16"/>',
        'check' => '<path d="M5 12l5 5L20 7"/>',
    ];
    if (!isset($paths[$name])) {
        return '';
    }
    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" '
         . 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
         . 'aria-hidden="true">' . $paths[$name] . '</svg>';
}

// ============================================================
// Функции для AJAX, дат, расписания и меню
// ============================================================

// Браузер просит ответ в формате JSON? (fetch с заголовком Accept)
function wants_json()
{
    return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
}

// Это запрос к API? (адрес содержит /api/ или браузер ждёт JSON)
function is_api_request()
{
    return strpos($_SERVER['REQUEST_URI'], '/api/') !== false || wants_json();
}

// Отправить JSON и завершить работу (для middleware и функций)
function json_exit($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// "2026-11-12" -> "12 ноября 2026, чт"
function date_ru($date)
{
    $months = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
               'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    $days = ['', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts)
         . ', ' . $days[(int)date('N', $ts)];
}

// "09:30:00" -> "09:30"
function time_short($time)
{
    return substr($time, 0, 5);
}

// "09:30:00" -> 570 (минут от начала суток)
function time_to_minutes($time)
{
    $p = explode(':', $time);
    return (int)$p[0] * 60 + (int)$p[1];
}

// 570 -> "09:30"
function minutes_to_time($minutes)
{
    return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
}

// Наибольший общий делитель (нужен, чтобы подобрать шаг сетки расписания)
function gcd_int($a, $b)
{
    $a = abs($a);
    $b = abs($b);
    while ($b > 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return $a;
}

function appointment_status_label($status)
{
    $labels = [
        'pending'   => 'Ожидает подтверждения',
        'confirmed' => 'Подтверждена',
        'rejected'  => 'Отклонена',
        'cancelled' => 'Отменена',
        'completed' => 'Завершена',
    ];
    return isset($labels[$status]) ? $labels[$status] : $status;
}

// Текущая страница входит в раздел меню? nav_active('/patients')
function nav_active($prefix)
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
        $path = substr($path, strlen(BASE_URL));
    }
    return strpos($path, $prefix) === 0;
}

// ------------------------------------------------------------
// Иконки в стиле "матовое стекло": синяя форма сзади + стеклянная спереди.
// Стили .gi, .s, .g, .w находятся в app.css
// ------------------------------------------------------------
function glass_icon($name)
{
    // Вспомогательная функция: одна фигура (класс, x, y, ширина, высота, скругление, текст, стиль)
    $shape = function ($class, $x, $y, $w, $h, $radius = '8px', $text = '', $style = '') {
        return '<b class="' . $class . '" style="left:' . $x . 'px;top:' . $y . 'px;width:' . $w . 'px;height:' . $h
             . 'px;border-radius:' . $radius . ';' . $style . '">' . $text . '</b>';
    };

    $html = '';
    switch ($name) {
        case 'cal':     // расписание
            $html = $shape('s', 16, 2, 9, 16, '5px') . $shape('s', 39, 2, 9, 16, '5px')
                  . $shape('g', 6, 10, 52, 50, '13px', '31', 'font-size:24px;padding-top:6px');
            break;
        case 'chk':     // мои записи
            $html = $shape('s', 16, 2, 9, 16, '5px') . $shape('s', 39, 2, 9, 16, '5px')
                  . $shape('g', 6, 10, 52, 50, '13px', '&#10003;', 'font-size:30px;padding-top:6px');
            break;
        case 'fold':    // картотека пациентов
            $html = $shape('s', 14, 6, 34, 18, '8px') . $shape('g', 6, 18, 52, 40, '12px');
            break;
        case 'users':   // пользователи
            $html = $shape('s', 38, 8, 20, 20, '50%') . $shape('s', 32, 32, 28, 22, '14px 14px 6px 6px')
                  . $shape('g', 10, 16, 20, 20, '50%') . $shape('g', 4, 38, 34, 22, '17px 17px 6px 6px');
            break;
        case 'eye':     // врачи
            $html = $shape('s', 24, 10, 30, 30, '50%') . $shape('g', 4, 20, 56, 32, '50%')
                  . $shape('w', 27, 28, 10, 10, '50%');
            break;
        case 'clock':   // график работы
            $html = $shape('s', 28, 4, 32, 32, '50%') . $shape('g', 4, 16, 48, 48, '50%')
                  . $shape('w', 26, 26, 4, 16, '2px') . $shape('w', 26, 38, 14, 4, '2px');
            break;
        case 'chart':   // отчёты
            $html = $shape('s', 38, 6, 18, 52, '7px') . $shape('g', 6, 32, 18, 26, '7px')
                  . $shape('g', 24, 18, 18, 40, '7px');
            break;
        case 'user':    // профиль
            $html = $shape('s', 18, 4, 28, 28, '50%') . $shape('g', 6, 26, 52, 34, '26px 26px 10px 10px');
            break;
        case 'lock':    // права доступа
            $html = $shape('s', 18, 4, 28, 34, '14px 14px 0 0', '', 'background:none;border:6px solid #1B4DFF;border-bottom:0')
                  . $shape('g', 6, 24, 52, 36, '12px') . $shape('w', 29, 38, 7, 7, '50%');
            break;
        case 'out':     // выход
            $html = $shape('s', 32, 6, 26, 50, '9px') . $shape('g', 4, 12, 40, 44, '12px', '&rarr;', 'font-size:26px');
            break;
        default:
            return '';
    }
    return '<span class="gi-box" aria-hidden="true"><span class="gi">' . $html . '</span></span>';
}

// Поводы обращения (значение в БД => название на экране)
function reason_options()
{
    return [
        'consultation' => 'Консультация',
        'diagnostics'  => 'Диагностика',
        'manipulation' => 'Манипуляция',
        'glasses'      => 'Подбор очков',
        'surgery'      => 'Операция',
    ];
}

function reason_label($code)
{
    $all = reason_options();
    return isset($all[$code]) ? $all[$code] : '';
}

// Адрес аватарки пользователя или пустая строка
function avatar_url($user)
{
    return !empty($user['avatar_path']) ? url($user['avatar_path']) : '';
}

// Первые буквы фамилии и имени (для круглой заглушки без фото)
function initials($person)
{
    return mb_strtoupper(mb_substr($person['last_name'], 0, 1) . mb_substr($person['first_name'], 0, 1));
}

// Русские буквы -> латиница (для адреса страницы врача: smirnova-elena)
function translit($text)
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
    ];
    $text = strtr(mb_strtolower($text), $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
