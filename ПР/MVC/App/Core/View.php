<?php

namespace App\Core;

class View{

    public static function render(string $template, array $data = []):void{
        
        extract($data);

        $viewFile = __DIR__.'/../Views/'.$template.'.php';
        
        if (!file_exists($viewFile)){
            http_response_code(500);
            exit("ХАХАХА, файла не существует, file not {$template}");

        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean(); //содержимое шаблона

        require __DIR__.'/../Views/layout.php'; //обертка


    }
}



?>