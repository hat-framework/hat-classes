<?php

namespace classes\Exceptions;
class modelException extends \Exception{
    public function __construct($model, $message = "") {
        $message = ($message == "")?"Erro ao executar o model":$message;
        $message = "$model: $message"; 
        parent::__construct($message, 401);
    }
}