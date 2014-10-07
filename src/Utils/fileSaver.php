<?php

namespace classes\Utils;

/**
 * Classe Manipuladora de arquivos de cache, os arquivos são salvos utilizando criptografia (se estiver disponível no php do servidor)
 * @author Thompson Moreira <tigredonorte@yahoo.com.br>
 */
class fileSaver{
    
    private $error = "";
    private $encrypt = "";
    private $ext = '';
    private $expires = '';
    protected $file_dir = '';

    public static function LoadFileSaver($dir, $encrypt = true, $ext = 'html'){
        static $obj = array();
        if(!isset($obj[$dir])){
            $o = new fileSaver($dir, $encrypt);
            $o->setFileExtension($ext);
            $obj[$dir] = $o;
        }
        return $obj[$dir];
    }
    
    public function setExpirationTime($minutes){
        $this->expires = $minutes;
    }
    
    public function setFileExtension($ext){
        $default   = ($this->ext !== "")?$this->ext:"html";
        $this->ext = ($ext == "")?$default:$ext;
    }
    
    public function __construct($path, $encrypt) {
        $this->file_dir = $path;
        $this->encrypt  = is_bool($encrypt)?$encrypt:true;
    }
    
    /**
     * Verifica se um cache existe
     * @param string $cache_name
     * @return boolean true caso exista, false caso contrário
     */
    public function exists($cache_name, $ext = ""){
        $this->setFileExtension($ext);
        $file = $this->getFileName($cache_name);
        if($this->expires === ""){
//            if(file_exists($file) === false){return false;}
//            $dt = date("Y-m-d H:i:s.", filemtime($file));
//            $diff = \classes\Classes\timeResource::diffDate($dt, "", "Mi");
//            if($diff > $this->expires){$this->delete($cache_name) === false;}
        }
        return(file_exists($file));
    }
    
    /**
     * Cria um cache a partir de um cachename e um conteudo
     * @param string $cache_name Nome do cache a ser salvo
     * @param mixed $conteudo Conteúdo do cache (será criptografado)
     * @return boolean
     */
    public function create($cache_name, $conteudo, $ext = ""){
        $this->setFileExtension($ext);
        $file = $this->getFileName($cache_name);
        if($this->encrypt)$conteudo = \classes\Classes\crypt::encrypt($conteudo);
        if(!$this->LoadResource('files/file')->savefile($file, $conteudo)){
            $this->error = $this->LoadResource('files/file')->getErrorMessage();
            return FALSE;
        }
        return TRUE;
    }
    
    public function prepareContent($conteudo){
        if($this->encrypt)$conteudo = \classes\Classes\crypt::encrypt($conteudo);
        return $conteudo;
    }
    
     /**
     * Apaga um diretorio de cache do sistema
     * @param string $cache_name
     * @return boolean true se apagar o cache, false caso contrário
     */
    public function deleteFolder($cache_name){
        $file = "$this->file_dir/$cache_name";
        getTrueDir($file);
        if(!$this->LoadResource('files/dir')->remove($file)){
            $this->error = $this->LoadResource('files/dir')->getErrorMessage();
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Apaga um cache do sistema
     * @param string $cache_name
     * @return boolean true se apagar o cache, false caso contrário
     */
    public function delete($cache_name, $ext = ""){
        $this->setFileExtension($ext);
        $file = $this->getFileName($cache_name);
        if(!$this->LoadResource('files/dir')->removeFile($file)){
            $this->error = $this->LoadResource('files/dir')->getErrorMessage();
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Retorna o conteúdo de um arquivo de cache
     * @param string $cache_name Nome do cache a ser recuperado
     * @return mixed false se o cache não existir, str se o cache existe
     */
    public function get($cache_name, $ext = ""){
        $this->setFileExtension($ext);
        $file = $this->getFileName($cache_name);
        try{
            if($this->encrypt){
                return \classes\Classes\crypt::decrypt(
                        $this->LoadResource('files/file')->GetFileContent($file)
                );
            }
            return $this->LoadResource('files/file')->GetFileContent($file);
        }catch (\Exception $e){
            $this->error = "Não foi possível recuperar o arquivo de cache. Código do erro: " .$e->getCode()." <br/> Mensagem: ".$e->getMessage();
            return false;
        }
    }
    
    /**
     * retorna a string contendo a causa de um erro
     * @return string
     */
    public function getError(){
        return $this->error;
    }
    
    public function setError($error){
        $this->error = $error;
    }


    public function LoadResource($resource){
        static $obj = NULL;
        if($obj == NULL){
            $obj = new \classes\Classes\Object();
        }
        return $obj->LoadResource($resource, 'res');
    }
    
    public function getFileName($cache_name){
        $file = str_replace($this->file_dir, '', "$cache_name.$this->ext");
        $f    = $this->file_dir . $file;
        getTrueDir($f);
        return $f;
    }
    
    public function append($cache_name, $conteudo){
        $file = $this->getFileName($cache_name);
        if(!$this->LoadResource('files/file')->append($file, $conteudo)){
            $this->error = $this->LoadResource('files/file')->getErrorMessage();
            return FALSE;
        }
        return TRUE;
    }
    
}