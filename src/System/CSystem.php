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
    	$file = \classes\Classes\Registered::getPluginLocation($modulo, true) . "/$controller/classes/$controller"."Controller.php";
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
        $file = \classes\Classes\Registered::getPluginLocation($modulo, true) . "/Config/$class.php";
        $this->LoadConfigFromPlugin($modulo);
        if(!file_exists($file)) return;
        require_once $file;
        $this->plloader = new $class();
        $this->plloader->setCommonVars();
        $this->setVars($this->plloader->getVars());
    }
    
    public function catchExcetion($code, $msg){
        if($code == 0){$code = 500;}
        $vars['filename'] = $this->findFile($code);
        $vars['erro']     = "$code $msg";
        $view = new \classes\Classes\View();
        $view->registerVars($vars);
        $view->execute('admin/exception/index');
        try{
            \classes\Utils\Log::save("Sytem/Exception", $_SERVER['REQUEST_URI']." - $code - $msg");
            \usuario_loginModel::user_action_log('exception', "erro:$code  msg:$msg");
        }catch (Exception $ee){
            die("Falha catastrófica! O sistema tentou recuperar de um erro $code e não conseguiu!");
        }
    }
    
    private function findFile($code){
        $location = "exceptions".DS."e$code.php";
        $file = \classes\Classes\Registered::getTemplateLocation(CURRENT_TEMPLATE, true).DS.$location;
        if(file_exists($file)){return $file;}
        
        $file = \classes\Classes\Registered::getTemplateLocation('core', true).DS.$location;
        return(file_exists($file))?$file:'';
    }
}