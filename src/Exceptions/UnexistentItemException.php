<?php

namespace classes\Exceptions;
class UnexistentItemException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"O registro que você está procurando não existe!":$message;
        parent::__construct($message);
    }
}