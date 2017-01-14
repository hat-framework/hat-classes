<?php

namespace classes\System;
use classes\Classes\session;
use classes\Classes\Object;
abstract class system extends Object {

    protected $url;
    protected $controller;
    protected $action;
    protected $vars;
    protected $newvars = array();
    protected $type = "";
    protected $template = "";
    protected $plloader = "";
    public function  __construct() {
        
        try{
            $this->LoadModel('usuario/login', 'lobj');
            $this->LoadModel('usuario/tag/usertag', 'utag');
        }  catch (\Exception $e){
            die($e->getMessage());
        }
        $this->init();
    }
    
             
             public function init(){
                $this->checkMobile();
                $this->setBaseData();
                $this->defineBaseData();
				$this->setMenu();
            }
            
                    private function checkMobile(){
                        if(!defined("MOBILE")) {
                           $this->LoadResource('mobile', 'mob');
                           $bool = $this->mob->IsMobile();
                           define("MOBILE", ($bool == true)?true:false);
                       }
                    }
                    
                    private function setBaseData(){
                        //seta a url
                        $this->url = (isset( $_GET['url'] )? $_GET['url'] : "$this->mdefault/$this->cdefault/index");
                        $explode = explode("/", $this->url);
                        //print_r($explode); if($explode[0] == 'p'){die('ronca');} else {die('foo');}
                        if (!defined("CURRENT_URL")) {define("CURRENT_URL", $this->url);}

                        //seta o array com explode
                        $this->modulo         =  array_shift($explode);
                        $this->controller     =  array_shift($explode);
                        $this->action         =  array_shift($explode);
                        $this->vars           =  $explode;
                        if($this->controller == ""){
                            $this->controller = "index";
                            $this->action     = "index";
                        }
                    }
                    
                    private function defineBaseData(){
                        if(!$this->PathExists($this->modulo, $this->controller, $this->action)){
                            if(!\classes\Classes\Registered::getPluginLocation($this->modulo, true)){
                                $this->action     = $this->controller;
                                $this->controller = $this->modulo;
                                $this->modulo     = MODULE_DEFAULT;
                            }
                            
                            if(!$this->PathExists($this->modulo, $this->controller, $this->action)){
                                array_push($this->vars, $this->action);
                                $this->action = $this->controller;
                                $this->controller = "index";
                            }
                        }
                        if (!defined("CURRENT_MODULE"))     {define("CURRENT_MODULE"    , $this->modulo);}
                        if (!defined("CURRENT_CONTROLLER")) {define("CURRENT_CONTROLLER", $this->controller);}
                        if (!defined("LINK"))               {define("LINK"              , CURRENT_MODULE . "/".CURRENT_CONTROLLER);}
                    }
                    
                    public function setMenu(){
                        $v = session::getVar('system_menu_superior');
                        if($v == ""){
                            $this->LoadModel('site/menu', 'smenu');
                            $menu = $this->smenu->getMenu();
                            $this->LoadJsPlugin('menu/dropdown', 'menu');
                            $this->menu->imprime();
                            $v = $this->menu->draw($menu, "menu");
                            session::setVar('system_menu_superior', $v);
                        }
                        \classes\Classes\EventTube::addEvent('menu-superior', $v);
                    }
    
    public function run(){
        $this->start();
        $this->setTags();
        $this->tokenAuth();
        
        $controlle = $this->getController();
        $ajax      = $this->ajaxCheck();
        $app       = $this->appCheck();
        $this->plloader();
        $this->loadController($controlle, $app, $ajax);
        if(true === $this->callExtension($this->action)){return;}
        
        $action    = $this->checkAction();
        $this->initializeCTRL($action);
        $this->LoadUserMenu();
        $this->callAction($action);
    }
    
            private function LoadUserMenu(){
                if($this->lobj->IsLoged()){
                    $this->lobj->userIsConfirmed();
                }
                $this->LoadComponent('usuario/login', 'ucomp')->setLoadMenu();
            }
            
            private function tokenAuth(){
                try{
                    if(!isset($_POST['utoken']) || !isset($_POST['userID'])){return false;}
                    if(\usuario_loginModel::CodUsuario() != 0){return true;}
                    if(trim($_POST['utoken']) == "" || trim($_POST['userID']) == ""){return false;}
                    return \usuario_loginModel::autenticate($_POST['userID'], $_POST['utoken']);
                } catch (Exception $ex) {
                    return false;
                }
            }
            
            private function setTags(){
                if(!isset($this->utag) || $this->utag === null){return;}
                try{
                    $cod_usuario = \usuario_loginModel::CodUsuario();
                    if($cod_usuario == 0){return;}
                    $this->utag->addTag(array(
                        'taggroup' =>'Usuário Ativo','tag_expires_time' => '30', 'tag'=>"Usuário Ativo"
                        ),$cod_usuario
                    );
                    $this->utag->addTag(array(
                        'taggroup' =>'Usuário Ativo','tag_expires_time' => '30', 'tag'=>"Usuário Ativo ". ucfirst(CURRENT_MODULE)
                        ), $cod_usuario
                    );
                } catch (Exception $ex) {}
            }
            
            private function getController(){
                if(!$this->PathExists($this->modulo, $this->controller, $this->action)){
                    $this->pagenotfound(__METHOD__, "Arquivo ($this->file) não encontrado");
                }
                require_once $this->file;

                //verifica se a classe existe
                $controlle = $this->controller . $this->type; 
                if(!class_exists($controlle)){
                    $this->pagenotfound(__METHOD__, "Classe ($controlle) não encontrada");
                }
                return $controlle;
            }
    
            private function ajaxCheck(){
                if(!isset($this->vars[0]) || $this->vars[0] !== 'ajax'){
                    define('AJAX_ENABLED', false);
                    return false;
                }
                array_shift($this->vars);
                define('AJAX_ENABLED', true);
                return true;
            }
            
            private function appCheck(){
                if($this->action !== "app" || !isset($this->vars[1])){
                    return '';
                }
                $app = $this->vars[1];
                unset($this->vars[1]);
                return $app;
            }
            
            private function loadController($controller, $app, $ajax){
                $this->class = new $controller($this->vars);
                $this->class->setCurrentApp($app);
                if($ajax){$this->class->enableAjax();}
            }
            
            private function callExtension($action){
                $class  = "{$action}Action";
                $dir    = DIR_BASIC . "/extensions/$this->modulo/$this->controller/$action/$class.php";
                getTrueDir($dir);
                if(!file_exists($dir)){return false;}
                require_once $dir;
                if(!class_exists($class, false)){return false;}

                $obj = new $class($this->vars);
                if(!method_exists($obj, 'execute')){return false;}
                
                $this->defineConstants($action);
                $this->security($this->class, $action);
                //$obj->setController($this->class);
                $obj->execute($this->newvars);
                return true;
            }
            
            private function checkAction(){
                $action    = $this->action;
                if(!method_exists($this->class, $action)){
                    array_push($this->vars, $action);
                    $this->class->setVars($this->vars);
                    $action = "index";
                }
                return $action;
            }
            
            public function initializeCTRL($action){
                $this->class->setVars($this->newvars);
                $this->class->setTemplate($this->template);
                if(method_exists($this->class, 'setMenu')) {$this->class->setMenu();}
                $this->defineConstants($action);
            }
            
            private function plloader(){
                if(!is_object($this->plloader)){return;}
                if(is_admin) {$this->plloader->beforeAdminLoad();}
                else {$this->plloader->beforeCommonLoad($this->newvars);}
                $this->setVars($this->plloader->getVars());
            }
            
            private function callAction($action){
                //if($this->callExtension($action) === true){return;}
                if(is_object($this->plloader)){$this->plloader->AfterRegister($this->newvars);}
                $this->class->setVars($this->newvars);
                $this->history();
                $this->class->AfterLoad();
                $this->security($this->class, $action);
                $this->class->BeforeLoad();
                $this->class->setBreadcrumb();
                if(is_object($this->plloader)){$this->plloader->AfterExecute($this->newvars);}
                $this->class->$action();
                $this->class->BeforeExecute();
            }
            
                    private function history(){
                        if(!isset($_SESSION['history'])){
                            $_SESSION['history'] = array('last' => MODULE_DEFAULT . "/index", 'atual' => LINK);
                        }
                        elseif($_SESSION['history']['atual'] != LINK && !isset ($_REQUEST['ajax'])){
                            $_SESSION['history']['last']  = $_SESSION['history']['atual'];
                            $_SESSION['history']['atual'] = LINK;
                        }
                    }
                    
                    public function security($class, $action){
                        $has = false;
                        if($this->lobj->setLastAccessOfUser()){$has = $this->lobj->has_permission_alterada();}
                        
                        if($this->controller == "") {$this->controller = 'index';}
                        $action_name = "$this->modulo/$this->controller/$action";
                        \usuario_loginModel::user_action_log();
                        $this->denyExternNonPublicRequisition($action_name);
                        $act_temp    = $action_name;
                        $this->LoadModel('usuario/perfil', 'perm');
                        $perm = $this->perm->hasPermission($act_temp, true, $has);
                        if(!defined('PERMISSION')) {define("PERMISSION", $perm);}
                        if($perm == 'n'){
                            if($this->lobj->IsLoged()) {throw new \classes\Exceptions\AcessDeniedException();}
                            else {$this->lobj->needLogin();}
                            return;
                        }
                        $this->LoadModel('plugins/action', 'act');
                        $this->act->geraMenu($this->modulo, $action_name); 
                        if($perm != "p") {return;}
                        if(!$class->hasPermission($action)) {throw new \classes\Exceptions\AcessDeniedException();}
                    }

                            private function denyExternNonPublicRequisition($action_name){
                                $this->LoadClassFromPlugin('usuario/perfil/perfilPermissions', 'pp');
                                if($this->pp->isPublic($action_name)){return true;}
                                $var = validaUrl();
                                if($var === true) {return true;}
                                die($var);
                            }

            
            private function defineConstants($action){
                //define a constante da página atual
                $act  = $action . "/";
                $page = "$this->modulo/$this->controller/$act";
                
                if (!defined("CURRENT_CANONICAL_PAGE")) {define("CURRENT_CANONICAL_PAGE"  , "$this->modulo/$this->controller/$action");}
                if (!defined("CURRENT_PAGE"))           {define("CURRENT_PAGE"  , $page);}
                if (!defined("CURRENT_ACTION"))         {define("CURRENT_ACTION", $action);}
            }
    
    public function setVars($vars){
        $this->newvars = array_merge($this->newvars, $vars);
    }
    
    public function pagenotfound($method, $extras = ""){
        $msg = "";
        if(DEBUG){
            $msg =  "<hr /> Arquivo de disparo:".__FILE__."  <br />
                  Método de disparo: $method<br />
                  $extras";
        }
        throw new \classes\Exceptions\PageNotFoundException($msg);
    }
    
    abstract function PathExists($modulo, $controller, $action);
    abstract function start();
}