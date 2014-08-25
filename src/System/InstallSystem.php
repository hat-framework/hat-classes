<?php

namespace classes\System;
class InstallSystem extends System {

    protected $url;
    protected $controller;
    protected $action;
    protected $vars;
    protected $type = "Admin";
    protected $template;
    
    public function __construct() {
        if(isset($_REQUEST['autoinsert'])){
            require_once DIR_BASIC."/admin/installation.php";
        }
        //seta as variaveis default
    	$this->mdefault = 'admin';
    	$this->cdefault = 'install';
        $this->template = CURRENT_TEMPLATE;
        
    }
    
    public function run(){
        $this->url = (isset( $_GET['url'] )? $_GET['url'] : "index");
        $vars      = explode("/",$this->url);
        $method    = array_shift($vars);
        $temp      = implode("/", $vars);
        DefConstant("CURRENT_URL"        , URL."admin/install.php?url=plugins/setup/$method");
        DefConstant("CURRENT_MODULE"     , 'plugins');
        DefConstant("CURRENT_CONTROLLER" , 'setup');
        DefConstant("CURRENT_PAGE"       , "plugins/setup/$method/$temp");
        DefConstant("LINK"               , CURRENT_MODULE . "/".CURRENT_CONTROLLER);
        $file = \classes\Classes\Registered::getPluginLocation("plugins", true) . "/setup/classes/setupAdmin.php";
        if(!file_exists($file)){
            throw new \classes\Exceptions\PageNotFoundException("Não foi possível encontrar o arquivo $file");
        }
        require_once $file;
        //$method = $this->method;
        $controller = new \setupAdmin($vars);
        $controller->setTemplate($this->template);
        if(!method_exists($controller, $method)) $method = 'index';
        if (!defined("CURRENT_ACTION")) define("CURRENT_ACTION", $method);
        $controller->AfterLoad();
        $controller->$method();
        $controller->BeforeExecute();
     
    }

    public function PathExists($modulo, $controller, $action){}
    public function start(){}
        
}