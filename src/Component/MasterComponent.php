<?php

namespace classes\Component;
class MasterComponent extends \classes\Classes\Object{
    
    protected $keeptags = false;
    public function __construct() {
        $this->LoadResource("html", "Html");
        $this->s = new showItemComponent($this);
    }
    
    protected function getEnumClasses(){
        $addclass = array();
        foreach($this->dados as $nm => $d){
            if(!is_array($d)) continue;
            if(array_key_exists('type', $d) && $d['type'] == 'enum'){
                $addclass[] = $nm;
            }
        }
        return $addclass;
    }
    
    protected function getEnumClass($addclass, $item){
        $class = "";
        if(!empty($addclass)){
            foreach($addclass as $var){
                if(array_key_exists($var, $item)){
                    $class .= " ". strip_tags($item[$var]);
                }
            }
        }
        return $class;
    }
    
    protected function set($method, $value){
        $method     = strtolower($method);
        $i          = 1;
        $var        = str_replace('set', "", $method, $i);
        $this->$var = $value;
        return $this;
    }
    
    protected function pode_exibir($model, $item){
        return $this->s->pode_exibir($model, $item);
    }
    
    protected function checkIsPrivate($dados, $name){
        return $this->s->checkIsPrivate($dados, $name);
    }
    
    protected function print_paginator_if_exists($obj){
        $model = $obj->getTable();
        $v = \classes\Classes\EventTube::getRegion("paginate_$model");
        \classes\Classes\EventTube::clearRegion("paginate_$model");
        if(empty($v)) return;
        $v = array_shift($v);
        echo $v;
    }
    
    protected function getActionLinks($model, $pkey, $item){
        $pkey = implode("/",$this->getPkeyValue($pkey, $item));
        $v = array();
        foreach($this->listactions as $name => $action){
            $link = $this->getActionOfLink($name, $action, $model, $pkey);
            if($link != "") $v[$name] = $link;
        }
        return($v);
    }
    
    protected function getPkeyValue($pkey, $item){
        $out = array();
        if(is_array($pkey)){
            foreach($pkey as $pk){
                if(isset($item["__$pk"])) $out[] = strip_tags ($item["__$pk"]);
                elseif(isset($item[$pk])) $out[] = strip_tags ($item[$pk]);
            }
        }elseif(isset($item[$pkey])) $out[] = strip_tags ($item[$pkey]);
        return $out;
    }
    
    protected function getActionOfLink($name, $action, $model, $pkey){
        $class = $url = "";
        if(strstr($action, "/") === false){
            $class = GetPlainName($action);
            $url = "$model/$action/$pkey";
        }else{
            $cl    = explode("/", $action);
            while(!empty($cl) && is_numeric(end($cl))){array_pop($cl);}
            $class = end($cl);
            $url   = "$action/$pkey";
        }
        return $this->Html->getActionLinkIfHasPermission($url, "$name",$class, "");
        if($link != "") $v[$name] = $link;
    }
    
    
    public function setListActions($listActions){
        return $this->set(__FUNCTION__, $listActions);
    }   
    
    public function formatType($name, $dados, $valor, $item = array()){
        $method = "format_$name";
        if(method_exists($this, $method)) {
            return trim($this->$method($valor, $dados, $item));
        }
        if(!array_key_exists($name, $dados)) {if(!is_array ($valor))return trim($valor); else return $valor;}
        if(!array_key_exists('type', $dados[$name])){
            return is_array($valor) ? $valor: trim($valor);
        }
        if(is_array($valor)) return $valor;
        switch ($dados[$name]["type"]){
            case 'date':
                if($valor == '0000-00-00') return "";
                $valor = \classes\Classes\timeResource::getFormatedDate($valor);
                break;
            case 'datetime': 
            case 'timestamp': 
                if($valor == '0000-00-00' || $valor == '0000-00-00 00:00:00') return "";
                $valor = \classes\Classes\timeResource::getFormatedDate($valor);
                break;
            case 'time': 
                if($valor == '00:00:00' || $valor == '00:00') return "";
                break;    
            case 'text':
            case 'varchar':
                if(!$this->keeptags){
                    $valor = strip_tags($valor, "<b><a><ul><li><ol><i><u>");
                    if(strlen($valor) <= MAX_STR_IN_LIST) break;
                    $valor = Resume($valor, MAX_STR_IN_LIST);
                }
                break;
            case 'bit': 
                $valor = ($valor == 1 || $valor === true)?"Sim":"Não";
                break;
            case 'enum':
                $valor = (isset($dados[$name]['options'][$valor]))?$dados[$name]['options'][$valor]: ucfirst($valor);
                break;
            case 'decimal':
                if(!is_numeric($valor)) break;
                if(!isset($dados[$name]['size'])) break;
                $e = explode(',', $dados[$name]['size']);
                $casas = end($e);
                if($casas == "") $casas = 2;
                $valor = number_format($valor, $casas, ',', '.');
                break;
        }
        return trim($valor);
    }
    
    public function keepTags($keep){
        $this->keeptags = is_bool($keep)?$keep:false;
        return $this;
    }
}
