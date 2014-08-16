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
    
    public static function getPluginLocation($plugin, $full_path = false){
        return self::getLocation($plugin, self::$plugins, $full_path);
    }
    
    public static function getResourceLocation($resource, $full_path = false){
        return self::getLocation($resource, self::$resources, $full_path);
    }
    
    public static function getTemplateLocation($templates, $full_path = false){
        return self::getLocation($templates, self::$templates, $full_path);
    }
    
    public static function getAllPluginsLocation($full_path = false){
        return self::getAllLocation(self::$plugins, $full_path);
    }
    
    public static function getAllResourcesLocation($full_path = false){
        return self::getAllLocation(self::$resources, $full_path);
    }
    
    public static function getAllTemplatesLocation($full_path = false){
        return self::getAllLocation(self::$templates, $full_path);
    }
    
    private static function getAllLocation($array, $full_path = false){
        if(false === $full_path){return $array;}
        foreach($array as &$value){
            $value = DIR_BASIC ."$value";
            getTrueDir($value);
        }
        return $array;
    }
    
    private static function getLocation($folder, $array, $full_path = false){
        $folder = (array_key_exists($folder, $array))?$array[$folder]:"";
        if($folder === "" || false === $full_path)return $folder;
        $folder = DIR_BASIC ."$folder";
        return getTrueDir($folder);
    }
}
