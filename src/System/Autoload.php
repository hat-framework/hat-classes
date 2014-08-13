<?php

spl_autoload_register(function ($nomeClasse) {

    $file = str_replace(array('/', '\\', '//'), DIRECTORY_SEPARATOR, DIR_BASIC . str_replace("_", "/", $nomeClasse).".php");
    //echo $file."<br/>\n";
    if (file_exists($file)) {
        require_once($file);
        return;
    }
        
    $files = array(SYSTEM, EXCEPTION, INTERFACES, SYSCLASSES, MODEL_CLASS, CONTROLLERS, COMPONENTS, FK_CLASSES);
    $debug = "";
    foreach($files as $file){
        $file = "$file$nomeClasse.php";
        if (file_exists($file)) {
            require_once($file);
            return;
        }
        $debug .= "Arquivo ($file) não existe <br />";
    }
    
    if(defined("CURRENT_MODULE") && defined("CURRENT_CONTROLLER")){
        $file = MODULOS . CURRENT_MODULE ."/" .CURRENT_CONTROLLER . "/classes/$nomeClasse.php";
        if (file_exists($file)) {
            require_once($file);
            return;
        }
    }
    
    if (DEBUG){
        error_log("Arquivo de disparo: (".SYSTEM."autoload.php) <br/>\n
             classe procurada: ($nomeClasse) <br />\n
             Localização: ( __autoload() ) <br/><br/>\n\n $debug <br/><hr />\n\n");
    }
});
