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
        return str_replace($keys, $val, $this->pages[$action]);
    }
}