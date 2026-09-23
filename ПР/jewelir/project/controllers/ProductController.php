<?php

namespace Project\Controllers;

use Core\Controller;
use Project\Models\Product;

class ProductController extends Controller{
    public function index(){ //список всех изделий
        $this->title = 'Ювелирные изделия';
        $products = (new Product)->getALL();
        return $this->render('product/index', ['products'=> $products]);

    }

    public function show($params){ //одно изделие
        $id = $params['id'];
        $product = (new Product)->getById($id);

        if (!$product){
            return $this->render('product/notfound', []);
        }


        $this->title = $product['name'];
        return $this->render('product/show', ['product'=> $product]);
    }
}

?>