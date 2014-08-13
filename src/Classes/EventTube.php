<?php
namespace classes\Classes;
class EventTube{

    static private $events = array();
    static private $locked = array();
    static public function addEvent($region, $obj){
        if(isset(self::$locked[$region]) && self::$locked[$region] == true) return;
        self::$events[$region][] = $obj;
    }
    
    static public function addMenu($region, $array, $jsplugin = 'menu/treeview'){
        if(isset(self::$locked[$region]) && self::$locked[$region] == true) return;
        if(!is_array($array) && DEBUG) die("EventTube::addMenu - O menu deve ser um array");
        if(!isset(self::$events['event_tube_menu'][$region])){
            self::$events['event_tube_menu'][$region] = array();
        }
        
        $i = count(self::$events['event_tube_menu'][$region]);
        self::$events['event_tube_menu'][$region][$i]['menu']   = $array;
        self::$events['event_tube_menu'][$region][$i]['plugin'] = $jsplugin;
    }
    
    static public function lockRegion($region){
        self::$locked[$region] = true;
    }
    
    static public function removeItemFromMenu($region, $nome_item){
        if(!isset(self::$events['event_tube_menu'][$region])) return false;
        $menu = self::$events['event_tube_menu'][$region];
        if(empty($menu)) return false;
        self::removeItemFromSubMenu($menu, $nome_item);
        self::$events['event_tube_menu'][$region] = $menu;
        return true;
    }
    
    static private function removeItemFromSubMenu(&$arr, $nome_item){
        if(!is_array($arr)) return;
        foreach ($arr as $name => $act){
            
            if($name === $nome_item) {
                //echo "$name - $nome_item<br/>\n";
                unset($arr[$name]);
                continue;
            }
            
            if(is_array($arr[$name]))self::removeItemFromSubMenu($arr[$name], $nome_item);
        }
    }
    
    static public function clearRegion($region){
        if(isset(self::$events[$region])) 
            unset(self::$events[$region]);
        if(isset(self::$events['event_tube_menu'][$region])) 
            unset(self::$events['event_tube_menu'][$region]);
        if(isset(self::$locked[$region]))
            unset(self::$locked[$region]);
    }

    static public function getRegion($region){
        
        if(isset(self::$events['event_tube_menu'][$region])){
            if(!isset(self::$events[$region])) self::$events[$region] = array();
            foreach(self::$events['event_tube_menu'][$region] as $ev){
                $plugin   = $ev['plugin'];
                $menu     = $ev['menu'];
                $obj      = new Object();
                $mn = $obj->LoadJsPlugin($plugin, 'mn');
                $mn->imprime();
                self::$events[$region][] = $mn->draw($menu);
            }
        }
        
        if(array_key_exists($region, self::$events))
            return self::$events[$region];
    }
    
    static public function Debug(){
        debugarray(self::$events);
    }
}

?>