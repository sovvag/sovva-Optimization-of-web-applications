<?php

//url

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

echo "<pre>";
echo "URL: {$_SERVER["REQUEST_URI"]} \n";
echo "Путь: {$uri} \n";
echo "Метод: {$_SERVER["REQUEST_URI"]} \n";
echo "</pre>";

spl_autoload_register(function(string $class){
    $prefix = 'App\\';
    $baseDir = __DIR__. '/app/';

    if (!str_starts_with($class, $prefix)) return;

    $file = $baseDir.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    echo $file. "<br>";
    if (file_exists($file)) require $file;


});

//простой маршрутизатор 

// switch($uri){
//     case '/':
//         echo "<h1> Главная страница </h1>";
//         break;
//     case '/about':
//         echo "<h1> О проекте </h1>";
//         break;
//     default:
//         if (preg_match('#^/article/(\d+)$#',$uri,$m)){
//             echo "<h1> Статья №{$m[1]} </h1>";
//         } else {
//             http_response_code(404);
//             echo "404 - ты дурачек ! ";
//         }

// }

//Простой маршрутизатор

use App\Controllers\ArticleControllers;
use App\Controllers\Store;
use App\Core\Router;


$router = new Router;   

// $router->get('/store', [Store::class, 'product_index']);
$router->get('/product/{id}', fn($id) => print "Товар ID: $id");

$router->get('/', [ArticleControllers::class, 'index']);
$router->get('/about', fn()=>print 'О нас');
$router->get('/corzina', fn()=>print 'Корзина');

$router->get('/article/{id}', [ArticleControllers::class, 'show']);

//запуск роутера
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

?>

<div>

<a href="/">Главная</a><br>
<a href="/about">О нас</a><br>
<a href="/corzina">Корзина</a><br>

</div>