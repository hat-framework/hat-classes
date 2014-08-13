<?php
namespace classes\Component;
use classes\Classes\EventTube;
abstract class listComponent extends CComponent{
    
    protected $model = "";
    protected $obj   = null;
    protected $dados = array();
    protected $pkey  = array();
    protected $id    = '';
    public function setModel($model){
        $this->LoadModel($model, 'obj');
        $this->model = $model;
        $this->dados = $this->obj->getDados();
        $this->pkey  = $this->obj->getPkey();
        $this->id    = str_replace("/", "_", $model);
        $this->findEnumClasses();
        $this->findAvaibleClasses();
    }
    
    protected $itens = array();
    public function setItens($itens){
        $this->itens = $itens;
    }
    
    protected $title = "";
    public function setTitle($title){
        $this->title = $title;
    }
    
    protected $class = "";
    public function setClass($class){
        $this->class = ($class == "")?"":" class='$class'";
    }
    
    private $addclass = array();
    private function findEnumClasses(){
        foreach($this->dados as $nm => $d){
            if(!is_array($d)) continue;
            if(array_key_exists('type', $d) && $d['type'] == 'enum'){
                $this->addclass[] = $nm;
            }
        }
    }
    
    protected function print_paginator_if_exists(){
        $model  = $this->obj->getTable();
        $region = EventTube::getRegion("paginate_$model");
        if(empty($region)) return;
        $v = array_shift($region);
        echo $v;
    }
    
    protected function checkIsPrivate($name){
        if(@$name[0] == "_" && @$name[1] == "_") return true;
        if(!array_key_exists($name, $this->dados)) return false;
        if(!is_array($this->dados[$name])) return true;
        if($this->dados[$name]['private'] === true) return true;
        
        if(!MOBILE) return false;
        if(@$this->dados[$name]['mobile_hide'] === false)  return false;
        return true;
    }
    
    protected function getEnumClass($item){
        
        if(empty($this->avaibleClasses)) return "";
        $class = "";
        foreach($this->avaibleClasses as $var){
            if(array_key_exists($var, $item)){
                $class .= " {$var}_{$item[$var]}";
            }
        }
        return $class;
    }
    
    private $avaibleClasses = array();
    private function findAvaibleClasses(){
        foreach($this->dados as $nm => $d){
            if(!is_array($d)) continue;
            if(array_key_exists('type', $d) && $d['type'] == 'enum'){
                $this->avaibleClasses[$nm] = $nm;
            }
        }
        return $this->avaibleClasses;
    }
    
    protected $showOnlyListItem = false;
    public function showOnlyListItem(){
        $this->showOnlyListItem = true;
    }
    
    abstract public function listar();
}

?>