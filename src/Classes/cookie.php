<?php

namespace classes\Classes;
class cookie{

    public static function create($cookiename, $time = 0){
        $ckname = self::getCookieName($cookiename);
        self::setCookie($ckname, '');
    }
    
    public static function destroy($cookiename){
        if(!self::exists($cookiename)) return true;
        $ckname =  self::getCookieName($cookiename);
        self::setCookie($ckname, '', -3600);
        if(isset($_COOKIE[$ckname])){unset($_COOKIE[$ckname]);}
        return self::exists($cookiename);
    }
    
    public static function destroyALL(){
        // unset cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                self::setCookie($name, '', -3600);
            }
        }
        
        //garantindo que os cookies serão destruídos
        $cookiess = $_COOKIE;
        if(is_array($cookiess)){
            foreach ($cookiess  as $key => $value ){
                self::destroy($key);
            }
        }
        $_COOKIE = array();
        return true;
    }
    
    public static function exists($cookiename){
        $ckname = self::getCookieName($cookiename);
        if(isset($_COOKIE[$ckname])){return true;}
        return(isset($_COOKIE[$cookiename]));
    }
    
    public static function setVar($cookiename, $value){
        if(!self::exists($cookiename)){self::create($cookiename);}
        $ckname = self::getCookieName($cookiename);
        $value  = \classes\Classes\crypt::encrypt_camp(json_encode($value));
        self::setCookie($ckname, $value);
    }
    
    public static function getVar($cookiename){
        if(!self::exists($cookiename)){return "";}
        $ckname = self::getCookieName($cookiename);
        return(isset($_COOKIE[$ckname]))?json_decode( \classes\Classes\crypt::decrypt_camp($_COOKIE[$ckname])):$_COOKIE[$cookiename];
    }
    
    public static function debugCookies(){
        if (!isset($_SERVER['HTTP_COOKIE'])) {return;}
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $nm    = array_shift($parts);
            $name  = \classes\Classes\crypt::decrypt(trim($nm));
            $var   = self::getVar($name);
            if(trim($var) === ""){continue;}
            echo "<hr/><b>$name:</b><br/>";
            if(is_array($var)) {print_rrh($var);}
            elseif(!base64_decode($var)) {echo $var;}
            else {echo base64_decode($var);}
        }
    }
    
    private static function setCookie($name, $value, $time = ""){
        if(!is_numeric($time) || $time <= 0){$time = 86400 * 365;}
        $name = urlencode($name);
        setcookie ($name, $value, time()+$time, "/");
        $_COOKIE[$name] = $value;
    }
    
    private static function getCookieName($cookiename){
        return urldecode(\classes\Classes\crypt::encrypt_camp($cookiename));
    }
}