<?php

namespace classes\Exceptions;
class UnexistentItemException extends \Exception{
    public function __construct($message = "", $code = '404') {
        $message = ($message == "")?"O registro que você está procurando não existe!":$message;
        parent::__construct($message, $code);
    }
}