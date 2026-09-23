<?php

namespace Project\Models;

use \Core\Model;

class Product extends Model{
     
    public function getALL(){ // Все изделия с названием материала
        return $this->findMany("		
                SELECT p.*, m.name AS material_name
				FROM products p
				JOIN materials m ON m.id = p.material_id
				ORDER BY p.name");
    }


    public function getById($id){
        $id = (int)$id;
			return $this->findOne("
				SELECT p.*, m.name AS material_name, m.price_per_gram
				FROM products p
				JOIN materials m ON m.id = p.material_id
				WHERE p.id = $id");
    }


}

?>