<?php

namespace classes\FK;
class fkContainer{
    
    private $card    = '';
    private $fkmodel = '';
    private $k1      = '';
    private $k2      = '';
    private $arr     = array();
    private $var     = '';
    private $name    = '';
    
    public function init($arr, $var, $name){
        $this->arr     = $arr;
        $this->fkmodel = $this->arr['fkey']['model'];
        $this->card    = $this->arr['fkey']['cardinalidade'];
        $this->k1      = array_shift($this->arr['fkey']['keys']);
        $this->k2      = array_shift($this->arr['fkey']['keys']);
        $this->name    = $name;
        $this->setVar($var);
    }
    
    public function setVar($var){
        $this->var = $var;
    }
    public function getVar(){
        return $this->var;
    }
    public function getFkmodel(){
        return $this->fkmodel;
    }
    public function getArr(){
        return $this->arr;
    }
    public function getK1(){
        return $this->k1;
    }
    public function getK2(){
        return $this->k2;
    }
    public function getCard(){
        return $this->card;
    }
    public function getName(){
        return $this->name;
    }
}

?>