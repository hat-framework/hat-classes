<?php

namespace classes\Interfaces;
use classes\Classes\Object;
abstract class ShowFkInterface extends Object{
    protected $cont = null;
    public final function __construct($cont) {
        $this->LoadResource('html', 'Html');
        $this->cont = $cont;
    }
    
    public abstract function exibir();
}

?>
