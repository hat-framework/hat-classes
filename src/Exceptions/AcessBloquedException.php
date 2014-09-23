<?php

namespace classes\Exceptions;
class AcessBloquedException extends \Exception{
    public function __construct($message = "") {
        $message = ($message == "")?"
            Esta página não pode ser exibida pois o seu perfil de usuário não lhe concede permissões para acessá-la":$message;
        parent::__construct($message, 401);
    }
}