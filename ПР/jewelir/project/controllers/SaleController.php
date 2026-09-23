<?php

namespace Project\Controllers;

use Core\Controller;
use Project\Models\Sale;

class SaleController extends Controller{

    public function index(){
            
        $this->title = 'Продажи';
        $sales = (new Sale)->getALL();
        return $this->render('sale/index', ['sales' => $sales]);
        
    }
}

?>