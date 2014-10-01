<?php

namespace classes\Utils;
class cache{
    
    public static $file_dir = DIR_CACHE;
    
    public static function getError(){
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->getError();
    }
    
    public static function setExpirationTime($minutes){
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        $obj->setExpirationTime($minutes);
    }
    
    public static function setFileExtension($extension){
        $obj = fileSaver::LoadFileSaver(self::$file_dir);
        $obj->setExpirationTime($extension);
    }
    
    public static function get($cache_name, $ext = "") {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->get($cache_name, $ext);
    }
    
    public static function delete($cache_name, $ext = "") {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->delete($cache_name, $ext);
    }
    
    public static function deleteFolder($folder_name) {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->deleteFolder($folder_name);
    }
    
    public static function create($cache_name, $conteudo, $ext = "") {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->create($cache_name, $conteudo, $ext);
    }
    
    public static function append($cache_name, $conteudo, $ext = "") {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->append($cache_name, $conteudo, $ext);
    }
    
    public static function exists($cache_name, $ext = ""){
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->exists($cache_name, $ext);
    }
    
}