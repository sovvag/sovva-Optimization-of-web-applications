<?php

namespace Project\Models;

use \Core\Model;


class Material extends Model{

    public function getALL(){
        return $this->findMany("SELECT * FROM materials ORDER BY name");
    }

    public function getById($id){
        $id = (int)$id;
        return $this->findOne("SELECT * FROM materials WHERE id = $id");
    }
}

?>