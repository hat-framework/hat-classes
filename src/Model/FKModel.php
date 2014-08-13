<?php

namespace classes\Model;
use classes\Classes\Object;
class FKModel extends Object{
    
    private $fk11 = array();
    private $fk1n = array();
    private $fkn1 = array();
    private $fknn = array();
    protected $ref = "";
    protected $fk = array();
    
    public function analiza($dados){
        
        if(empty ($dados)){
            throw new \classes\Exceptions\modelException(__CLASS__, "O array de dados não pode ser vazio");
        }
        
        foreach($dados as $name => $d){
            if(array_key_exists("fkey", $d)){
                $card = $d['fkey']['cardinalidade'];
                if    ($card == "11") $this->fk11[$name] = $d;
                elseif($card == "1n") $this->fk1n[$name] = $d;
                elseif($card == "n1") $this->fkn1[$name] = $d;
                elseif($card == "nn") $this->fknn[$name] = $d;
            }
        }
        
        return true;
    }
    
    public function set($fk, $ref_model){        
        //chave estrangeira a ser analizada
        $this->fk = $fk;
        
        //model que está chamando
        $this->ref = $ref_model;
    }
    
    public function get11(){
        return $this->fk11;
    }
    
    public function get1n(){
        return $this->fk1n;
    }
    
    public function getn1(){
        return $this->fkn1;
    }
    
    public function getnn(){
        return $this->fknn;
    }
    
    public function getFk(){
        return $this->fk;
    }
}

?>