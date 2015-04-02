<?php

namespace classes\Controller;
class Action extends \classes\Classes\Object{
    
    protected $variaveis = array();
    private   $tags 	 = array();   
    private   $template	 = "";
    private   $ajax 	 = false;
    public final function display($action, $vars = array()){
        //seta as variaveis
        $this->setVars($vars);
        //carrega a view
        $this->view = new \classes\Classes\View();
        $this->view->registerVars($this->variaveis);
        $this->view->setTags($this->tags);
        $this->view->setTemplate($this->template);
        if($this->ajax) {$this->view->enableAjax();}
        $this->view->execute($action);
    }
    
    public final function setVars($vars){
        if(is_array($vars) && !empty($vars)){
            $this->variaveis = array_merge($vars, $this->variaveis);
        }
    }
    
    public final function getVars(){
        return $this->variaveis;
    }
}