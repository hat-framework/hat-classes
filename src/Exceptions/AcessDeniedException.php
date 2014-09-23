<?php

namespace classes\Exceptions;
class AcessDeniedException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"Você não tem permissão para acessar esta página":$message;
        parent::__construct($message, 403);
    }
}