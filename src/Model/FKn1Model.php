<?php

namespace classes\Model;
class FKn1Model extends FKModel{
    
    //trata dados das chaves estrangeiras
    public function inserir(&$post, $lastid, $pkey, $id = ""){
        
        $bool = true;
        if(empty ($this->fk)){return true;}
        //para cada campo do tipo n1
        foreach($this->fk as $name => $array){
            $name             = str_replace(array("[", "]"), "", $name);
            $arr              = $post;
            $model            = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            $dados            = array_keys($this->tmp_model->getDados());
            foreach($dados as $key){
                if(!array_key_exists($key, $arr)){continue;}
                $assoc[$key] = $arr[$key];
            }
            $key = array_shift(array_keys($assoc));
            $max = count($assoc[$key]);
            while($max > 0){
                $max--;
                $insert = array();
                foreach($assoc as $key => $valor){
                    $insert[$key] = $valor[$max];
                }
                $insert[$pkey] = $lastid;
                $boolean = ($id == "")?$this->tmp_model->inserir($insert):$this->tmp_model->editar($id, $insert);
                if(!$boolean){
                    $this->setMessage($name, $this->tmp_model->getErrorMessage());
                    $bool = false;
                }
            }
        }
        return $bool;
    }
    
    public function editar(&$post, $lastid, $pkey, $id){
        return $this->inserir($post, $lastid, $pkey, $id);
    }
    
    
    public function apagar($id){
        
    }
   
    
    public function selecionar($campo_src, $array, $campo, $cod_item, $model_name, $orderby, $link = ''){
        $tb = $this->LoadModel($array['model'], "model")->getTable();
        if(defined("CURRENT_ACTION") && CURRENT_ACTION == "sublist"){return;}
        
        $camp = $this->getCampo($campo,$tb, $model_name);
        if(is_array($camp)){return $camp;}
        
        
        $where  = $this->getWhere($cod_item, $camp, $tb, $array);
        $limit  = isset($array['limit'])?$array['limit']:10;
        $page   = isset($_SESSION['page'])?$_SESSION['page']:"1";
        $lk     = ($link == "")?"$model_name/sublist/$cod_item/$campo_src":$link;
        return $this->model->paginate($page, $lk, "", "", $limit, array(), $where, $orderby);
    }
    
            private function getWhere($cod_item, $camp, $tb, $array){
                $pkey  = $this->model->getPkey();
                $where = ($cod_item == "")? "" : $this->model->genWhere("$tb`.`$camp", $pkey, $cod_item);
                if(array_key_exists('filther', $array)){
                    $where = ($where == "")?$array['filther']:"$where AND ".$array['filther'];
                }
                return $where;
            }
    
            private function getCampo($campo,$tb, $model_name){
                $dados  = $this->model->getDados();
                if(array_key_exists($campo, $dados)){return $campo;}
                $end_loop = false;
                foreach($dados as $name_dado => $value_dado){
                    if(!array_key_exists('fkey', $value_dado)) {continue;}
                    //note que estou pesquisando no outro model, então a verificação deve ser 1 -> n mesmo!
                    //echo "($tb - $name_dado - $model_name - ".$value_dado['fkey']['model'].")<br/>";
                    if($value_dado['fkey']['model'] != $model_name) {continue;}
                    $campo = $name_dado;
                    $end_loop = true;
                }
                if($end_loop){return $campo;}
                if(!DEBUG){return array();} 
                throw new \classes\Exceptions\modelException(
                        __CLASS__, 
                        "O atributo do tipo n -> 1 do model $tb de nome $campo não foi encontrado"
                );
            }
}