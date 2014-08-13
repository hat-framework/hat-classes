<?php

namespace classes\Component;
class gridComponent extends MasterComponent{
    
    protected $modelname = "";
    protected $model     = null;
    protected $class     = "";
    protected $title     = "";
    protected $itens     = array();
    protected $dados     = array();
    protected $id        = '';
    protected $pkey      = '';

    public function __construct($component) {
        parent::__construct();
        $this->gui = new GUI();
    }
    
    public function listar(){
        $this->openList();
        $this->listAll();
        $this->print_paginator_if_exists($this->model);
        $this->closeList();
        $this->itens = array();
    }
    
    public function openList(){
        echo "<div{$this->class}> ";
                if(!empty($this->itens)) $this->gui->subtitle($this->title);
                echo "<ul id='$this->id' class='list'>";
    }
    
    public function listAll(){
        $addclass   = $this->getEnumClasses();
        if(!is_array($this->itens) || empty ($this->itens)){return;}
        foreach($this->itens as $item){
            if(!$this->pode_exibir($this->modelname, $item)) {continue;}
            $class = $this->getEnumClass($addclass, $item);
            echo "<li class='list-item list_$this->id $class'>";
                $this->DrawItem($item);
            echo "</li>";
        }
    }
    
    public function closeList(){
                echo "</ul>";
        echo "</div>";
    }
    
    private function getId(){
        $id = "";
        if(is_array($this->pkey)){
            $v = "";
            foreach($this->pkey as $pk){
                if(!array_key_exists($pk, $this->cu)) continue;
                $id .= $v.$this->cur_item[$pk];
                $v  = "-";
            }
        }elseif(array_key_exists($this->pkey, $this->cur_item)){$id = $this->cur_item[$this->pkey];}
        return ($id != "")?" id='$id'":'';
    }
    
    private function DrawSubItem($name, $it){
        if($this->checkIsPrivate($this->dados, $name)) {return;}
        $it = $this->formatType($name, $this->dados, $it, $this->cur_item);
        if(is_array($it) && isset($this->dados[$name]['fkey'])){
             $this->LoadComponent($this->dados[$name]['fkey']['model'], 'md');
             $this->md->listar($this->dados[$name]['fkey']['model'], $it);
        }elseif(!is_array($it)){
            echo "<span class='$name'>$it</span>";
        }
        else $this->show($this->model, $it);
    }
    
    protected function DrawItem($item){
        if(empty ($item)) {return;}
        $this->cur_item = $item;
        $links = $this->getActionLinks($this->modelname, $this->pkey, $this->cur_item);
        $id    = $this->getId();
        $lini  = $linf = "";
        $link  = (is_array($links))? implode(" ", $links):"";
        
        echo "<div class='container'$id>$lini";
        foreach($this->cur_item as $name => $it){
            $this->DrawSubItem($name, $it);
        }
        echo "$link $linf </div>";
    }
    
    public function setModel($model){
        $this->LoadModel($model, 'model');
        $this->modelname = $model;
        $this->dados     = $this->model->getDados();
        $this->pkey      = $this->model->getPkey();
        $this->id        = str_replace("/", "_", $model);
        return $this;
    }
    
    public function setItens($item){
        return $this->set(__FUNCTION__, $item);
    }
    
    public function setTitle($title){
        return $this->set(__FUNCTION__, $title);
    }
    
    public function setClass($class){
        $class = ($class != "")? " class='$class'":"";
        return $this->set(__FUNCTION__, $class);
    }
}