<?php

namespace classes\Model;
class DataModel extends \classes\Classes\Object{
    protected $dados = array();
    protected $hasFeatures = false;
    public function getDados(){
        return ($this->hasFeatures)?$this->filtherFeatures($this->dados):$this->dados;
    }
    
    public final function filtherFeatures($dados){
        foreach($dados as $name => $arr){
            if(!array_key_exists('feature', $arr)){continue;}
            if(!defined($arr['feature'])){
                unset($dados[$name]);
                continue;
            }
            $value = constant($arr['feature']);
            if($value !== true){unset($dados[$name]);}
        }
        return $dados;
    }
}