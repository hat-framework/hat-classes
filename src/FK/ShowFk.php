<?php

namespace classes\FK;
use classes\Classes\Object;
class ShowFk extends Object{

    private $cont = null;
    public function __construct() {
        $this->cont = new fkContainer();
    }
    public function exibir($arr, $var, $name){
        $this->cont->init($arr, $var, $name);
        $card = $this->cont->getCard();
        $class = "\classes\FK\ShowFk$card";
        $obj = new $class($this->cont);
        $obj->exibir();
        return $this->cont->getVar();
    }
}