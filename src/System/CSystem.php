<?php

namespace classes\System;
class CSystem extends System {

    protected $url;
    protected $controller;
    protected $action;
    protected $vars;
    protected $type = "Controller";
    
    public function __construct(){
    	$this->mdefault = MODULE_DEFAULT;
    	$this->cdefault = "index";
    	parent::__construct();
    }
    
    public function PathExists($modulo, $controller, $action){
    	$file = Registered::getPluginLocation($modulo, true) . "/$controller/classes/$controller"."Controller.php";
    	$this->file = $file;
    	return file_exists($file);
    }
    
    public function start(){

        //checa se o plugin está disponível
        $this->LoadModel("plugins/plug", "plug");
        $this->plug->IsAvaible($this->modulo);
       
        if(isset ($_REQUEST['ajax'])) return;
        $this->loadLoader($this->modulo);
        
    }
    
    private function loadLoader($modulo){
        $class = $modulo."Loader";
        $file = Registered::getPluginLocation($modulo, true) . "/Config/$class.php";
        $this->LoadConfigFromPlugin($modulo);
        if(!file_exists($file)) return;
        require_once $file;
        $this->plloader = new $class();
        $this->plloader->setCommonVars();
        $this->setVars($this->plloader->getVars());
    }
}

?>