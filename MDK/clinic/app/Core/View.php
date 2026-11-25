<?php
// ============================================================
// View.php: показ страниц (шаблонов).
// View::render('doctors/show', ['doctor' => $row]) подключит файл
// app/Views/doctors/show.php и вставит его в общий макет.
// ============================================================
class View
{
    public static function render($view, $data = [], $layout = 'main')
    {
        // Ошибки и старые значения формы (если были) передаём в шаблон
        $data['errors'] = pull('errors', []);
        $data['old']    = pull('old', []);

        extract($data);   // ключи массива становятся переменными: $doctor, $title...

        // 1. Выполняем сам шаблон и запоминаем результат в $content
        ob_start();
        require APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();

        // 2. Вставляем $content в макет (шапка, подвал и т.д.)
        require APP_PATH . '/Views/layouts/' . $layout . '.php';
    }
}
