<?php

namespace classes\Model;
class FK1nModel extends FKModel{
        
    //trata dados das chaves estrangeiras
    public function inserir(&$post, $id = ""){
        return true;
    }
    
    public function editar(&$post, $id){
        return $this->inserir($post, $id);
    }
    
    public function apagar($post){
        return true;
    }
    
    public function selecionar($item, $array, $campo, $cod_item){
        $this->LoadModel($array['model'], "model");
        $k1 = array_shift($array['keys']);
        $k2 = array_shift($array['keys']);
        $where = "`$k1` = '$item'";
        if(array_key_exists('filther', $array) && !is_array($array['filther'])){
            $where .= " AND ". $array['filther'];
        }
        $var = $this->model->selecionar(array($k1, $k2), $where, 1);
        if(empty ($var)) return "";
        $var = array_shift($var);
        $out[$var[$k1]] = $var[$k2];
        return $out;
    }
    
    public function filther($filther){
        if(is_array($filther)){
            $query = array();
            
            foreach($filther as $filtro){
                extract($filtro);
                $val = isset($_SESSION[$session])?$_SESSION[$session]:$value;
                if($val == "") continue;
                $query[] = "`$camp`='$val'";
            }
            return implode(" AND ", $query);
        }return $filther;
    }
}

?>