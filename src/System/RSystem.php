<?php

namespace classes\System;
use classes\Classes\Object;
class RSystem extends Object{

    protected $url;
    protected $controller;
    protected $action;
    protected $vars;
    protected $newvars = array();
    protected $type = "";
    protected $template = "";
    
    public function __construct() {
    	$this->mdefault = 'admin';
    	$this->cdefault = 'index';
        $this->template = TEMPLATE_ADMIN;

        //faz a instalacao do banco de dados caso seja necessario
        $this->LoadModel('plugins/setup', 'iobj');
        $this->iobj->setup();
    }
    
    public function start(){
        $this->LoadModel('usuario/login', 'uobj');
        if(!$this->uobj->UserIsAdmin()){
            $this->uobj->needAdminLogin($redirect = "");
        }
    }

    public function init(){

        //seta a url
        $this->url = (isset( $_GET['url'] )? $_GET['url'] : "$this->mdefault/$this->cdefault/index");
        DefConstant("CURRENT_URL", $this->url);

        //seta o array com explode
        $explode = explode("/", $this->url);
        $this->modulo         =  array_shift($explode);
        $this->controller     =  array_shift($explode);
        $this->action         =  array_shift($explode);
        $this->vars           =  $explode;

        if($this->controller == ""){
            $this->controller = "index";
            $this->action     = "index";
        }

        if(!$this->PathExists($this->modulo, $this->controller, $this->action)){
            $this->action = $this->controller;
            $this->controller = "index";
        }

        DefConstant("CURRENT_MODULE"     , $this->modulo);
        DefConstant("CURRENT_CONTROLLER" , $this->controller);
        DefConstant("CURRENT_PAGE"       , $this->modulo . "/" .$this->controller . "/". $this->action);
    }

    public function PathExists($modulo, $controller, $action){
    	$file = RESOURCES . "$modulo/pages/$modulo/$controller/$controller"."Controller.php";
    	$this->file = $file;
        echo $file;
    	return file_exists($file);
    }

    public function run(){

        $this->start();
        if(!$this->PathExists($this->modulo, $this->controller, $this->action)){
             $this->pagenotfound(__METHOD__, "Arquivo ($this->file) não encontrado");
        }
        require_once $this->file;

        //verifica se a classe existe
        $controller = $this->controller . $this->type;
        if(!class_exists($controller)){
            $this->pagenotfound(__METHOD__, "Classe ($controller) não encontrada");
        }

        //verifica se o método existe
        $class = new $controller($this->vars);
        $class->setVars($this->newvars);
        $action = $this->action;
        if(!method_exists($class, $action)){
            $action = "index";
        }
        $class->setTemplate($this->template);
        $class->$action();
    }

    public function setVars($vars){
        $this->newvars = array_merge($this->newvars, $vars);
    }
        
}

?>
