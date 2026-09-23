<?php

namespace App\Core;

use App\Core\Controller;

class Router {

    //Таблица маршрутов
    public array $routes = [];

    public function get(string $path, callable|array $handler ):void{
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler ):void{
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void{

        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $route => $handler){
            $pattern = '#^'. preg_replace('#\{(\w+)}#', '(\d+)', $route).'$#';

            if(preg_match($pattern, $uri, $matches)){
                array_shift($matches);

                if (is_array($handler)){
                    [$class, $action] = $handler;
                    (new $class())->$action($matches);
                } else {
                    $handler($matches);
                }
                return;
            }

        }
        http_response_code(404);
        echo '404 вам отжиманий';
    }

}


?>