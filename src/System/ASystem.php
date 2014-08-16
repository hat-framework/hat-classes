<?php

namespace classes\System;
class ASystem extends System {

    protected $url;
    protected $controller;
    protected $action;
    protected $vars;
    protected $type = "Admin";
    protected $template;
    
    public function __construct() {

        //seta as variaveis default
    	$this->mdefault = 'admin';
    	$this->cdefault = 'index';
        $this->template = TEMPLATE_ADMIN;
        
        //construtor pai
        parent::__construct();
    }

    public function PathExists($modulo, $controller, $action){
    	$file = \classes\Classes\Registered::getPluginLocation($modulo, true) . "/$controller/classes/$controller"."Admin.php";
    	$this->file = $file;
    	return file_exists($file);
    }
    
    public function start(){
        
        //checa a conexao com o banco de dados
        $this->LoadModel("plugins/setup", 'setup');
        $this->setup->checkInstalation();
        
        $this->LoadModel('usuario/login', 'uobj');
        $page = CURRENT_MODULE . "/" . CURRENT_CONTROLLER . "/";
        if(!$this->uobj->UserIsAdmin())$this->uobj->needAdminLogin($page);
        if(isset ($_REQUEST['ajax']))return;

        //carrega o menu superior
        $this->LoadModel("plugins/plug", "model");
        $arr = array();
        if(!isset ($_REQUEST['ajax'])){
            $arr['module_menu'] = $this->model->listPlugins();
            $this->setVars($arr);
        }
        
        $class = $this->modulo . "Loader";
        $file = \classes\Classes\Registered::getPluginLocation($this->modulo, true) . "/Config/$class.php";
        if(!file_exists($file))return;
        
        require_once $file;
        $menu_obj = new $class();
        $menu_obj->setAdminVars();
        $vars = $menu_obj->getVars();
        $this->setVars($vars);
    }
    
    public function LoadFeatures(){
        if(isset($_SESSION['sysfeatues'])) return;
        $this->LoadModel("plugins/plug", "model");
        $plugins = $this->model->listPlugins();
        $plugins = array_keys($plugins);
        $avaible = array();
        foreach($plugins as $plug){
            $avaible[$plug] = true;
            $file = \classes\Classes\Registered::getPluginLocation($plug, true)."/Config/features.php";
            if(!file_exists($file)) {continue;}
            require_once $file;
        }
        
        $defines = get_defined_constants('user');
        $defines = $defines['user'];
        
        $out = array();
        foreach($defines as $name => $def){
            $var = explode("_", $name);
            $this->toArray($var, $out);
        }
    }
    
    private function toArray($feature_arr, &$out = array()){
        if(empty ($feature_arr)) return 1;
        $i = array_shift($feature_arr);
        if(!array_key_exists($i, $out)) $out[$i] = array();
        $this->toArray($feature_arr, $out[$i]);
    }
        
}