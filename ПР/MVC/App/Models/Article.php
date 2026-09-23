<?php

namespace App\Models;

use App\Core\Database;

class Article {

    public function all() : array {
        return Database::pdo()
        ->query("SELECT * FROM articles")
        ->fetchALL();
    }

    public function find(int $id): ?array {
        $stm = Database::pdo()->prepare("SELECT * FROM articles WHERE id = ?");
        $stm ->execute([$id]);


        
        return $stm->fetch() ?:null;
    }
}

?>