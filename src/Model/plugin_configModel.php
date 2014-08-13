<?php

namespace classes\Model;
use classes\Classes\Object;
class plugin_configModel extends configModel {

    protected $title       = "";
    protected $description = "";
    protected $filename    = "";
    protected $grupo       = "";
    protected $dados       = array();
    
    public function __construct() {
        $temp = explode("/", $this->filename);
        $file = str_replace("Config", "", array_pop($temp)) ;
        array_pop($temp);
        $this->filename = implode("/", $temp)."/$file";
    }

    public final function getTitle(){
        return $this->title;
    }
    
    public final function getDescription(){
    	return $this->description;
    }

    public function getDados(){
        if(!empty($_POST)){
            foreach($_POST as $name => $val){
                if(array_key_exists($name, $this->dados))
                    $this->dados[$name]['default'] = $val;
            }
        }  
        return $this->dados;
    }
    
    public function getGrupo(){
    	return $this->grupo;
    }

}

?>