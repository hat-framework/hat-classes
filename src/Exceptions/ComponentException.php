<?php

namespace classes\Exceptions;
class ComponentException extends \Exception{
    public function __construct($plugin, $message = "") {
        $message = ($message == "")?"Erro ao executar plugin":$message;
        $message = "$plugin - $message"; 
        parent::__construct($message, 504);
    }
}