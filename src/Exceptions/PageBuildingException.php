<?php

namespace classes\Exceptions;
class PageBuildingException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"Página em construção ":$message;
        parent::__construct($message, 501);
    }
}