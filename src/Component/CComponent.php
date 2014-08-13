<?php

namespace classes\Component;
use classes\Classes\Object;
class CComponent extends Object{
    
    protected $listActions = array();
    protected function formatType($name, $valor, $item = array()){
        
        $method = "format_$name";
        if(method_exists($this, $method)) return trim($this->$method($valor, $this->dados, $item));
        
        if(!array_key_exists($name, $this->dados)) {
            if(!is_array ($valor))return trim($valor); 
            else return $valor;
        }
        
        if(is_array($valor)) return $valor;
        if(!array_key_exists('type', $this->dados[$name])) return trim($valor);
        
        /*
         * Quando os blocos forem implementados, substituir esta parte pela exibição de
         * cada classe do bloco.
         */
        switch ($this->dados[$name]["type"]){
            case 'date':
                if($valor == '0000-00-00') return "";
                $valor = \classes\Classes\timeResource::Date2StrBr($valor, false);
                break;
            case 'datetime': 
            case 'timestamp': 
                if($valor == '0000-00-00' || $valor == '0000-00-00 00:00:00') return "";
                $valor = \classes\Classes\timeResource::Date2StrBr($valor);
                break;
            case 'text':
            case 'varchar':
                $valor = strip_tags($valor, "<b><a><ul><li><ol><i><u>");
                if(strlen($valor) > MAX_STR_IN_LIST) $valor = Resume($valor, MAX_STR_IN_LIST);
                break;
            case 'bit': 
                $valor = ($valor == 1 || $valor === true)?"Sim":"Não";
                break;
            case 'enum':
                $valor = (isset($this->dados[$name]['options'][$valor]))?$this->dados[$name]['options'][$valor]: ucfirst($valor);
                break;
        }
        return trim($valor);
    }
    
    protected function getActionLinks($item){
        $pkey = implode("/",$this->getPkeyValue($item));
        $v    = array();
        foreach($this->listActions as $name => $action){
            $class = GetPlainName($action);
            $link  = (strstr($action, "/") === false)?"$this->model/$action/$pkey":"$action/$pkey";
            $url   = $this->Html->getActionLinkIfHasPermission($link, "$name",$class, "");
            if($url == "") continue;
            $v[$name] = $url;
        }
        return($v);
    }
    
    protected function getPkeyValue($item){
        $out = array();
        if(is_array($this->pkey)){
            foreach($this->pkey as $pk){
                if(isset($item["__$pk"])) $out[] = $item["__$pk"];
                elseif(isset($item[$pk])) $out[] = $item[$pk];
            }
        }elseif(isset($item[$this->pkey])) $out[] = $item[$this->pkey];
        return $out;
    }
    
}

?>