<?php

namespace classes\Classes;
class session{
    
    /**
    * @uses Contém a instância do banco de dados
    */
    private static $sessionname = "session_resource";
    private static $sessionarr = "sres";
    private static $sessioncache = array();
    
    public static function create($sessionname, $time = 0){
        $sessionname =  \classes\Classes\crypt::encrypt($sessionname);
        $_SESSION[self::$sessionarr][$sessionname] = "";
        if($time > 0){
            self::create(self::$sessionname, 0);
            $var = $this->getVar(self::$sessionname);
            $var[$sessionname]['time'] = $time;
            $var[$sessionname]['date'] = new DateTime();
            self::setVar(self::$sessionname, $var);
        }
    }
    
    public static function destroy($sessionname){
        if(!self::exists($sessionname)) return true;
        $enc_sessionname =  \classes\Classes\crypt::encrypt($sessionname);
        unset($_SESSION[self::$sessionarr][$enc_sessionname]);
        unset(self::$sessioncache[$enc_sessionname]);
        return self::exists($sessionname);
    }
    
    public static function destroyALL(){
        $_SESSION[self::$sessionarr] = array();
        self::$sessioncache = array();
        return true;
    }
    
    public static function exists($sessionname){
        $sessionname =  \classes\Classes\crypt::encrypt($sessionname);
        if(!isset($_SESSION[self::$sessionarr])) $_SESSION[self::$sessionarr]= array();
        return(array_key_exists($sessionname, $_SESSION[self::$sessionarr]));
    }
    
    public static function setVar($sessionname, $value){
        if(!self::exists($sessionname))
            self::create($sessionname);
        $sessionname =  \classes\Classes\crypt::encrypt($sessionname);
        $_SESSION[self::$sessionarr][$sessionname] =  \classes\Classes\crypt::encrypt(serialize($value));
        self::$sessioncache[$sessionname] = $value;
    }
    
    public static function getVar($sessionname){
        if(!self::exists($sessionname)) return "";
        $sessionname =  \classes\Classes\crypt::encrypt($sessionname);
        if(!isset(self::$sessioncache[$sessionname])){
            self::$sessioncache[$sessionname] = 
                (unserialize( \classes\Classes\crypt::decrypt($_SESSION[self::$sessionarr][$sessionname])));
        }
        return self::$sessioncache[$sessionname];
    }
    
    public static function debugSessions(){
        $keys = array_keys($_SESSION['cres']);
        foreach($keys as $name){
            $name =  \classes\Classes\crypt::decrypt($name);
            $var  = self::getVar($name);
            echo "<hr/>$name<br/><br/>";
            if(is_array($var)) debugarray($var);
            elseif(!base64_decode($var)) echo $var;
            else echo base64_decode($var);
        }
    }
}