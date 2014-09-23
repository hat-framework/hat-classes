<?php

namespace classes\Exceptions;
class CannotInstanceJsException extends \Exception{
    public function __construct($class_src, $class_dst) {
        $message = "$class_src - Não foi possível instanciar o Plugin javascript $class_dst";
        parent::__construct($message, 504);
    }
}