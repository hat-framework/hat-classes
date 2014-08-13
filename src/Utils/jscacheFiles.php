<?php

namespace classes\Utils;
/**
 * JS Cache na pasta P/Static/Files
 */
class jscacheFiles{
    
    public static $file_dir = DIR_FILES;
    
    public static function Load(){
        return fileSaver::LoadFileSaver(self::$file_dir, false, 'js');
    }
    
    public static function getError(){
        return self::Load()->getError();
    }
    
    public static function get($cache_name) {
        return self::Load()->get($cache_name);
    }
    
    public static function delete($cache_name) {
        return self::Load()->delete($cache_name);
    }
    
    public static function create($cache_name, $conteudo) {
        $conteudo = (is_array($conteudo))?json_encode($conteudo):$conteudo;
        return self::Load()->create($cache_name, $conteudo, 'js');
    }
    
    public static function exists($cache_name){
        return self::Load()->exists($cache_name);
    }
    
    public static function getUrl($cache_name){
        if(!self::exists($cache_name)) return '';
        return URL_JS . str_replace(array("\\\\", "\\"), '/', $cache_name . '.js');
    }
    
}