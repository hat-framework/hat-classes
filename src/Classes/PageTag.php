<?php

namespace classes\Classes;
class PageTag extends Object{
    protected $pages;
    public function getTagsOfPage($action, $item = array()){
        if(!isset($this->pages[$action])){return array();}
        if(empty($item)){return $this->pages[$action];}
        $keys = array_keys($item);
        foreach($keys as &$k){$k = "%$k%";}
        $val  = array_values($item);
        foreach($val as $name => &$v){
            if(!is_array($v)){continue;}
            $v = (isset($v["__$name"]))?$v[$v["__$name"]]:array_shift($v);
            if(!is_array($v)){continue;}
            unset($val[$name]);
            unset($keys[$name]);
        }
        return str_replace($keys, $val, $this->pages[$action]);
    }
}