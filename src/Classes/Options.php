<?php

namespace classes\Classes;
class Options extends Object{
    protected $files   = array();
    protected $menu   = array();
    
    public function getFile($action_name){
        if(!array_key_exists($action_name, $this->files)){return array();}
        return $this->files[$action_name];
    }
    
    public function getFiles(){
        return $this->files;
    }
    
    public function getMenu(){
        return $this->menu;
    }
}