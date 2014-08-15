<?php

namespace classes\Classes;
class Registered{
    
    private static $plugins   = array();
    private static $templates = array();
    private static $resources = array();
    private static $init      = false;
    
    public function init($registered) {
        if(empty($registered) || self::$init === true){return;}
        self::$plugins   = isset($registered['plugins'])  ?$registered['plugins']  :array();
        self::$resources = isset($registered['resources'])?$registered['resources']:array();
        self::$templates = isset($registered['templates'])?$registered['templates']:array();
        self::$init = true;
    }
    
    public static function getPluginLocation($plugin){
        return self::getLocation($plugin, self::$plugins);
    }
    
    public static function getResourceLocation($resource){
        return self::getLocation($resource, self::$resources);
    }
    
    public static function getTemplateLocation($templates){
        return self::getLocation($templates, self::$templates);
    }
    
    public static function getAllPluginsLocation(){
        return self::$plugins;
    }
    
    public static function getAllResourcesLocation(){
        return self::$resources;
    }
    
    public static function getAllTemplatesLocation(){
        return self::$templates;
    }
    
    private static function getLocation($folder, $array){
        return(array_key_exists($folder, $array))?$array[$folder]:"";
    }
}
