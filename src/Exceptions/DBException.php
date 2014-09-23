<?php

namespace classes\Exceptions;
class DBException extends \Exception{
    
    public function __construct($message, $code = 504) {
        $msg = "<hr />Database: <br/>$message<hr />";
        parent::__construct($msg, $code);
    }
    
}