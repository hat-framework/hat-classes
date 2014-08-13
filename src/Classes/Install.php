<?php

namespace classes\Classes;
abstract class Install extends Object{

    protected $dir  = "";
    protected $menu = array();
    private   $action;
    public function __construct() {
        $this->LoadResource("database", 'db');
    }
    
    abstract public function setup();
    abstract public function unsetup();
    abstract public function dependences();
    
    public final function setAction($action){
        $arr = array("install", "unstall");
        if(!in_array($action, $arr)){
            throw new \Exception("Ação $action não existe");
        }
        
        $this->action = $action;
    }
    
    public function sql($file){

        //carrega o recurso
        $this->LoadResource("files/file", "file");

        //cria o sql do modulo
        $file .= "$this->action.sql";
        if(!file_exists($file)){
            $this->setErrorMessage("O arquivo $file não foi encontrado");
            return false;
        }

        //pega o conteudo do arquivo .sql
        $conteudo = $this->file->GetFileContent($file);
        $querys = explode(";", $conteudo);
        array_pop($querys);
        $erro = "";

        //insere cada query uma a uma
        foreach($querys as $q){
            if(!$this->db->ExecuteInsertionQuery($q . ";")){
                $error = $this->db->getErrorMessage();
                if($error != "" && $erro == ""){
                    $erro = $error;
                }
            }
        }
        
        //retorna em caso de erro
	$this->setErrorMessage($erro);
        return ($erro == "");
    }
    
    public final function execute(){
        if($this->action == "install"){
            return $this->setup();
        }else{
            return $this->unsetup();
        }
    }
    
    public final function GetMenu(){
        return $this->menu;
    }
    
    protected function CreateFolder($folder, $modulo){
        
        //cria a pasta de imagens que será usada pelo ckeditor
        $name   = strtolower($folder);
        $modulo = strtolower($modulo);
        $this->LoadResource("files/dir", "dir");
        if($this->action == "install"){
            $bool = $this->dir->create($name, $modulo);
        }else{
            $bool = $this->dir->remove($name.$modulo);
        }
        if(!$bool){
            $this->setErrorMessage($this->dir->getErrorMessage());
            return false;
        }
        return true;
    }
}

?>