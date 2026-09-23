<?php

namespace App\Controllers;

use App\Core\Controller;

class Categories extends Controller{

    public function product_index():void {
        $product = [
            ['id' => 1, 'categories' => 'Одежда'],
            ['id' => 2, 'categories' => 'Обувь'],
            ['id' => 3, 'categories' => 'Электроника'],
            ['id' => 4, 'categories' => 'Красота'],
            ['id' => 5, 'categories' => 'Спорт'],
        ];

        echo "<h1>Выберите категорию товаров: </h1>";
        foreach ($product as $b){
            echo "<p>{$b['categories']} - <a href='/product/{$b['id']}'>{$b['categories']}</a></p>";

        }
    }


    public function show(array $categories):void{
        //заглушка
        echo "<h1>Категория товаров {$categories[0]}</h1>";
        echo "<p>Товары будут добавлены позже, у разраба закончился кофе, \nминус вайбик дальше кодить</p>";
        echo "<a href="/">назад</a>";

    }
}


?>