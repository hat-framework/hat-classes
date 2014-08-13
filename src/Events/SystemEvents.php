<?php
namespace classes\Classes;
use classes\Classes\EventTube;
class SystemEvents{

    static private $events = array();
    static private $locked = array();
    static public function send($eventname, $newData){
        if(isset(self::$locked[$region]) && self::$locked[$region] == true) return;
        self::$events[$region][] = $obj;
    }
}