<?php

namespace classes\Classes;
abstract class InstallPlugin extends Object{
    
    protected $dados = array();
    public final function getDados(){
        return $this->dados;
    }
    
    protected $app = array();
    public final function getApps(){
        return $this->app;
    }
    
    public function isDefault(){
        if(!isset($this->dados['isdefault'])) return false;
        return ($this->dados['isdefault'] == 's');
    }
    
    public function isSystem(){
        if(!isset($this->dados['system'])) return false;
        return ($this->dados['system'] == 's');
    }
    
    abstract public function install();
    abstract public function unstall();
    public function update(){
        return $this->importData();
    }
    
    protected $import = array();
    public function importData(){
        if(empty($this->import)){return true;}
        foreach($this->import as $model => $dados){
            if(!isset($dados) || !is_array($dados) || empty($dados)){continue;}
            if(false === $this->propagateMessage($this->LoadModel($model, 'rec') , 'importDataFromArray', $dados)) return false;
        }
        return true;
    }
}