<?php
namespace classes\Component;
class tableListComponent extends listComponent{
    
    private $table = array();
    public function listar(){
        $this->mount();
        $this->render();
    }
    
    private $drawHeaders = false;
    public function setDrawHeaders($drawHeaders){
        if(!is_bool($drawHeaders)) throw new \classes\Exceptions\ComponentException(__METHOD__ .": Atributo draw headers tem que ser um array");
        $this->drawHeaders = $drawHeaders;
    }
    
    private $header = array();
    public function setHeader($header){
        $this->header = $header;
    }
    
    private $header = array();
    private function findHeader(){
        $keys = array();
        if(!empty($this->itens)){
            $first = end($this->itens);
            $keys  = array_keys($first);
        }else $keys = array_keys ($this->dados);
        
        foreach($keys as $name){
            if($this->checkIsPrivate($this->dados, $name)) continue;
            if(!isset($this->dados [$name]['name'])) continue;
            $this->header[] = $this->dados [$name]['name'];
        }
        if(!empty($this->listActions)){
            $this->header[] = "Ações";
        }
    }
    
    private function mount(){
        $i = 0;
        $this->findHeader();
        if(!empty($this->itens)){
            foreach($this->itens as $item){
                $tb = array();
                foreach($item as $name => $valor){
                    if($this->checkIsPrivate($name)) continue;
                    $tb[$name] = $this->formatType($name, $valor, $item);
                }
                $links = $this->getActionLinks($item);
                
                $tb["Ações"] = (is_array($links))? implode(" ", $links):"";
                if($tb["Ações"] == "") unset($tb["Ações"]);
                $tb["__id"]  = implode("-", $this->getPkeyValue($item));
                $tb["__class"] = $this->getEnumClass($item);
                
                $this->table[$i] = $tb;
                $i++;
            }
        }
    }
    
    private function render(){
        echo "<div$this->class id='$this->id'> ";
            $this->gui->subtitle($this->title);
            $this->LoadResource('html/table', 'tb');
            if($this->drawHeaders) $this->tb->forceDrawHeaders();
            $this->tb->draw($this->table, $this->header);
            $this->print_paginator_if_exists();
        echo "</div>";
    }
    
}

?>