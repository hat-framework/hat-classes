<?php

namespace classes\Exceptions;
class PageBlockedException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"Esta página está bloqueada ou não foi instalada corretamente":$message;
        parent::__construct($message, 503);
    }
}