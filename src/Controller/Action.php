<?php

namespace classes\Controller;
abstract class Action extends \classes\Classes\Object{
    
    protected $variaveis = array();
    private   $tags 	 = array();   
    private   $template	 = "";
    private   $ajax 	 = false;
    protected $ctrl      = null;
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
    
    abstract public function execute($vars);

    public final function setVars($vars){
        if(is_array($vars) && !empty($vars)){
            $this->variaveis = array_merge($vars, $this->variaveis);
        }
    }
    
    public final function getVars(){
        return $this->variaveis;
    }
    
    public function setController($ctrl){
        $this->ctrl = $ctrl;
    }
    
    public function callCtrlAction($action, $vars, $view = ""){
        $this->ctrl->setVars($vars);
        $this->ctrl->AfterLoad();
        $this->ctrl->BeforeLoad();
        $this->ctrl->setBreadcrumb();
        $this->ctrl->setViewName($view);
        $this->ctrl->$action();
        $this->ctrl->BeforeExecute();
    }
}