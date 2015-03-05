<?php

namespace classes\Exceptions;
class InvalidArgumentException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"
            O argumento passado para a classe é inválido!":$message;
        parent::__construct($message, 500);
    }
}