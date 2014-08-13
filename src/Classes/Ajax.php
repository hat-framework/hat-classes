<?php
namespace classes\Classes;
class Ajax{
    
    private $attr = array(
        'url'      => CURRENT_URL,
        'dataType' => "'json'",
        'type'     => "'POST'",
        'data'     => '',
    );
    
    private $html;
    public function __construct() {
        $obj = new Object();
        $this->html = $obj->LoadResource('html', 'html');
    }

    public function setType($param){
        $this->set('type', "'$param'");
    }

    public function setUrl($param){
        $this->set('url', "'".$this->html->getLink($param)."'");
    }
    
    public function setDataType($param){
        $this->set('dataType', "'$param'");
    }
    
    public function setData($param){
        $this->set('data', " { $param } ");
    }
    
    public function set($attrname, $param){
        $this->attr[$attrname] = $param;
    }
    
    public function get($attrname){
        return $this->attr[$attrname];
    }
    
    public function ajax($function){
        
        if($this->html->getActionLinkIfHasPermission($this->get('url'), '') == "")return;
        $v    = "";
        $ajax = "$.ajax({ ";
        foreach($this->attr as $name => $value){
            $ajax .= "$v$name: $value";
            $v = ", ";
        }
        $ajax .= "});";
        $this->html->LoadJQueryFunction(str_replace("%ajax%", $ajax, $function));
    }
}


?>