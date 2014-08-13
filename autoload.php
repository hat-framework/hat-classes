<?php

require 'vendor/autoload.php';
spl_autoload_register(function ($nomeClasse) {
    $class = str_replace(array('/', '\\', '//'), DIRECTORY_SEPARATOR, str_replace("_", "/", $nomeClasse));
    $file  = dirname(__FILE__)."src".DIRECTORY_SEPARATOR."$class.php";
    if(file_exists($file)){
        require_once $file;
        return true;
    }
    return false;
});
