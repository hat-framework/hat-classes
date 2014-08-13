<?php
namespace classes\Classes;
class View extends Object{
    
    private $vars = array();
    private $tags = array();
    private $template = "";
    private $ajax = false;
    public final function setTemplate($template){
        $this->template = $template;
    }
    
    public final function registerVars($vars){
        $this->vars = $vars;
    }
    
    public final function setTags($tags){
        $this->tags = $tags;
    }
    
    public final function enableAjax(){
        $this->ajax = true;
    }
    
    public final function execute($action){
        
        if(array_key_exists("ajax", $_REQUEST)){
            die(json_encode($this->vars));
        }
        
        if(method_exists($this, $action)){
        	$this->$action();
        }
        $template = new Template($this->template);
        $template->registerTags($this->tags);
        if($this->ajax) $template->enableAjax();
        $template->registerVars($this->vars);
        $template->execute($action);
    }
 
}

?>