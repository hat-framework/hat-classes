<?php

namespace classes\Classes;
use classes\Classes\cookie;
class cookie{
    
    /**
    * @uses Contém a instância do banco de dados
    */
    private static $cookiename = "cookie_resource";
    private static $cookiearr = "cres";
    private static $cookiecache = array();
    
    public static function create($cookiename, $time = 0){
        $cookiename =  \classes\Classes\crypt::encrypt($cookiename);
        $_SESSION[cookie::$cookiearr][$cookiename] = "";
        if($time > 0){
            cookie::create(cookie::$cookiename, 0);
            $var = $this->getVar(cookie::$cookiename);
            $var[$cookiename]['time'] = $time;
            $var[$cookiename]['date'] = new DateTime();
            cookie::setVar(cookie::$cookiename, $var);
        }
    }
    
    public static function destroy($cookiename){
        if(!cookie::cookieExists($cookiename)) return true;
        $enc_cookiename =  \classes\Classes\crypt::encrypt($cookiename);
        unset($_SESSION[cookie::$cookiearr][$enc_cookiename]);
        unset(cookie::$cookiecache[$enc_cookiename]);
        return cookie::cookieExists($cookiename);
    }
    
    public static function destroyALL(){
        $_SESSION[cookie::$cookiearr] = array();
        cookie::$cookiecache = array();
        return true;
    }
    
    public static function cookieExists($cookiename){
        $cookiename =  \classes\Classes\crypt::encrypt($cookiename);
        if(!isset($_SESSION[cookie::$cookiearr])) $_SESSION[cookie::$cookiearr]= array();
        return(array_key_exists($cookiename, $_SESSION[cookie::$cookiearr]));
    }
    
    public static function setVar($cookiename, $value){
        if(!cookie::cookieExists($cookiename))
            cookie::create($cookiename);
        $cookiename =  \classes\Classes\crypt::encrypt($cookiename);
        $_SESSION[cookie::$cookiearr][$cookiename] =  \classes\Classes\crypt::encrypt(serialize($value));
        cookie::$cookiecache[$cookiename] = $value;
    }
    
    public static function getVar($cookiename){
        if(!cookie::cookieExists($cookiename)) return "";
        $cookiename =  \classes\Classes\crypt::encrypt($cookiename);
        if(!isset(cookie::$cookiecache[$cookiename])){
            cookie::$cookiecache[$cookiename] = 
                (unserialize( \classes\Classes\crypt::decrypt($_SESSION[cookie::$cookiearr][$cookiename])));
        }
        return cookie::$cookiecache[$cookiename];
    }
    
    public static function debugCookies(){
        $keys = array_keys($_SESSION['cres']);
        foreach($keys as $name){
            $name =  \classes\Classes\crypt::decrypt($name);
            $var  = cookie::getVar($name);
            echo "<hr/>$name<br/><br/>";
            if(is_array($var)) debugarray($var);
            elseif(!base64_decode($var)) echo $var;
            else echo base64_decode($var);
        }
    }
    
    /*retorna true caso um cookie tenha expirado*/
    private static function cookieExpired($cookiename){
        die("a ser implementado");
    }
    
}

?>