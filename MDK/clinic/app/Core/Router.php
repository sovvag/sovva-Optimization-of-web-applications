<?php
// ============================================================
// Router.php: по адресу в браузере находит нужный контроллер.
// ============================================================
class Router
{
    private $routes = [];

    public function get($path, $handler, $options = [])
    {
        $this->routes[] = ['GET', $path, $handler, $options];
    }

    public function post($path, $handler, $options = [])
    {
        $this->routes[] = ['POST', $path, $handler, $options];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Берём путь из адреса и убираем папку проекта (BASE_URL)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (BASE_URL !== '' && strpos($uri, BASE_URL) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            list($routeMethod, $path, $handler, $options) = $route;

            if ($routeMethod !== $method) {
                continue;
            }

            // {slug} в маршруте превращаем в шаблон поиска: ([^/]+)
            $pattern = preg_replace('#\{[a-z_]+\}#', '([^/]+)', $path);

            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches);   // убираем полное совпадение, остаются параметры

                $this->runMiddleware($method, $options);

                // 'DoctorController@show' -> класс и метод
                list($controllerName, $action) = explode('@', $handler);
                $controller = new $controllerName();
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        // Ничего не нашли - ошибка 404
        http_response_code(404);
        View::render('errors/404', ['title' => 'Страница не найдена', 'noindex' => true]);
    }

    // Проверки перед запуском контроллера
    private function runMiddleware($method, $options)
    {
        if ($method === 'POST') {
            CsrfMiddleware::handle();           // у каждой формы должен быть токен
        }
        if (!empty($options['auth'])) {
            AuthMiddleware::handle();           // нужен вход
        }
        if (!empty($options['perm'])) {
            RoleMiddleware::handle($options['perm']);   // нужно право
        }
    }
}
