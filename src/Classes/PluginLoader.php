<?php

namespace classes\Classes;
abstract class PluginLoader extends Object{
    
    private $vars = array();
    public function __construct() {
        
        $expl = explode("/", CURRENT_URL);
        if(isset($expl[2])){
            if($expl[2] == 'formulario'){
                $this->LoadModel('usuario/login', 'uobj');
                if(!$this->uobj->IsLoged())$this->uobj->needLogin();
            }
        }
    }
    public final function setVar($name, $valor){
        $this->vars[$name] = $valor;
    }
    
    public final function getVars(){
        return $this->vars;
    }
    
    abstract public function setCommonVars();
    public function beforeCommonLoad($vars){}
    
    abstract public function setAdminVars();
    public function beforeAdminLoad($vars){}
    
    public function AfterExecute($vars){}
}

?>