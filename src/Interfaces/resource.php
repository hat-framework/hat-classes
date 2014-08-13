<?php

namespace classes\Interfaces;
use classes\Classes\Object;
class resource extends Object{
    
   /**
    * @uses guarda o nome do diretório atual
    */
    protected $dir = "";
    
    //tipo da interface implementada,
    protected $type = "";
	
    private static $instances = array();
    public static function getInstanceOf(){
        $class_name = self::whoAmI();
        if (!isset(self::$instances[$class_name])){self::$instances[$class_name] = new $class_name(); }
        return self::$instances[$class_name];
    }
    
    public function __contruct(){
    	$class = $this->type . "Config";
    	$this->LoadResourceFile("classes/$class.php", false);
    }    
    /**
    * Este método retorna a instância do objeto
    * @uses Carregar uma nova instância
    * @return retorna uma nova instância
    */
    //public static function getInstanceOf();
    
    /**
    * @abstract Carrega um arquivo localizado dentro da pasta do recurso
    * @uses carregar um único arquivo dentro do recurso
    * @throws DBException
    * @return não retorna nada
    */
    public function LoadResourceFile($file, $throw = true){
        
        $file = "$this->dir/$file";
        if(file_exists($file)){
            require_once($file);
            return true;
        }
        if($throw){
            if(!DEBUG){
                $exp = explode("/", $file);
                $file = end($exp);
            }
            throw new \Exception("Não foi possível carregar o arquivo ($file)");
        }
        return false;
    }
}