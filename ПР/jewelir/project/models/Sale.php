<?php

namespace Project\Models;

use Core\Model;

class Sale extends Model{


    public function getALL(){
        
        return $this->findMany("
        		SELECT s.*, p.name AS product_name
				FROM sales s
				JOIN products p ON p.id = s.product_id
				ORDER BY s.sale_date DESC");
    }
}

?>