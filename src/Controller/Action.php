<?php

namespace classes\Controller;
class Action extends \classes\Controller\Controller{
    
    protected $variaveis = array();
    private   $tags 	 = array();   
    private   $template	 = "";
    private   $ajax 	 = false;
    protected $ctrl      = null;
    
    public function index() {
        $this->execute($this->variaveis);
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