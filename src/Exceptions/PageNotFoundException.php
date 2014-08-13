<?php

namespace classes\Exceptions;
class PageNotFoundException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"A página que você procura não existe":$message;
        parent::__construct($message, 404);
    }
}