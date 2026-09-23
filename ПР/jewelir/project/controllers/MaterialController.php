<?php

namespace Project\Controllers;

use Project\Models\Material;
use Core\Controller;

class MaterialController extends Controller{
    
    public function index(){

        $this->title = 'Материалы';
        $materials = (new Material)->getALL();
        return $this->render('material/index', ['materials' => $materials]);
    }
}

?>