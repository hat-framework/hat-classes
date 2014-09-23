<?php

namespace classes\Exceptions;
class JqgridException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"Jqgrid: Erro no plugin":$message;
        parent::__construct($message, 504);
    }
}