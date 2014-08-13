<?php

namespace classes\Model;
class FKn1Model extends FKModel{
    
    //trata dados das chaves estrangeiras
    public function inserir(&$post, $lastid, $pkey, $id = ""){
        
        $bool = true;
        if(empty ($this->fk)) return true;
        //para cada campo do tipo n1
        foreach($this->fk as $name => $array){
            
            $name             = str_replace(array("[", "]"), "", $name);
            $arr              = $post;
            $model            = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            $dados            = $this->tmp_model->getDados();
            foreach($dados as $key => $value){
                if(array_key_exists($key, $arr))
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
                if($id == "") $boolean = $this->tmp_model->inserir($insert);
                else          $boolean = $this->tmp_model->editar($id, $insert);
                //print_r($insert);
                //echo $this->tmp_model->db->getSentenca();
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
        $this->LoadModel($array['model'], "model");
        $tb     = $this->model->getTable();
        if(defined("CURRENT_ACTION") && CURRENT_ACTION == "sublist"){
            //if(DEBUG) echo "<div class='info'>Elementos da lista $tb não selecionados pois o método atual é o sublist</div>";
            return;
        }
        
        $pkey   = $this->model->getPkey();
        $dados  = $this->model->getDados();
        //echo $campo;
        if(!array_key_exists($campo, $dados)){
            $end_loop = false;
            foreach($dados as $name_dado => $value_dado){
                if(!array_key_exists('fkey', $value_dado)) continue;
                //note que estou pesquisando no outro model, então a verificação deve ser 1 -> n mesmo!
                //echo "($tb - $name_dado - $model_name - ".$value_dado['fkey']['model'].")<br/>";
                if($value_dado['fkey']['model'] != $model_name) continue;
                $campo = $name_dado;
                $end_loop = true;
            }
            if(!$end_loop){
                if(DEBUG) throw new \classes\Exceptions\modelException(__CLASS__, 
                        "O atributo do tipo n -> 1 do model $tb de nome $campo não foi encontrado"
                );
                else return array();
            }
        }
//        echo $campo;
        $where = ($cod_item == "")? "" : $this->model->genWhere("$tb`.`$campo", $pkey, $cod_item);
        if(array_key_exists('filther', $array)){
            $where = ($where == "")?$array['filther']:"$where AND ".$array['filther'];
        }
        
        $limit = isset($array['limit'])?$array['limit']:10;

        //$campos = $this->model->getCampos();
        $page = isset($_SESSION['page'])?$_SESSION['page']:"1";
        $link   = ($link == "")?"$model_name/sublist/$cod_item/$campo_src":$link;
        return $this->model->paginate($page, $link, "", "", $limit, array(), $where, $orderby);
    }
}

?>