<?php

namespace classes\Classes;
class Actions extends Object{
    protected $actions     = array();
    protected $permissions = array();
    protected $perfis      = array();
    
    public function getAction($action_name){
        if(!array_key_exists($action_name, $this->actions)){return array();}
        return $this->actions[$action_name];
    }
    
    public final function getActions(){
        return $this->actions;
    }
    
    public final function getPermissions(){
        return $this->permissions;
    }
    
    public final function getMenu($action){
        if(!isset($this->actions[$action]["menu"])) return array();
        return $this->actions[$action]['menu'];
    }
    
    public final function getPerfis(){
        return $this->perfis;
    }
}