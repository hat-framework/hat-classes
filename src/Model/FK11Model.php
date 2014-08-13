<?php

namespace classes\Model;
class FK11Model extends FKModel{
        
    //trata dados das chaves estrangeiras
    public function inserir(&$post, $allpost){

        if(empty ($this->fk)) return true;
        foreach($this->fk as $name => $array){
            
            $arr = $allpost;

            //carrega o model
            $model = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            
            //insere um novo item apenas se ele não existir
            if(!isset($post[$name]) || !is_array($post[$name])){
                //insere os dados
                if(!$this->tmp_model->inserir($arr)){

                    //se o campo for do tipo required, gera a mensagem de erro
                    if(array_key_exists('notnull', $array) && $array['notnull']){
                        $this->setErrorMessage($this->tmp_model->getErrorMessage());
                        return false;
                    }
                }else $post[$name] = $this->tmp_model->getLastId();
            }else{
                $this->tmp_model->fkediting();
                if(!$this->tmp_model->editar($post[$name], $allpost)){
                    //se o campo for do tipo required, gera a mensagem de erro
                    if(array_key_exists('notnull', $array) && $array['notnull']){
                        $this->setErrorMessage($this->tmp_model->getErrorMessage());
                        return false;
                    }
                    
                }
            }
            
        }
        return true;
    }
    
    public function editar(&$post, $id){
        if(empty ($this->fk)) return true;
        
        
        $keys = array_keys($this->fk);
        $this->LoadModel($this->ref, 'refmodel');
        $item = $this->refmodel->getSimpleItem($id, $keys);
        foreach($this->fk as $name => $array){
            
            //prepara as variaveis
            $model = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            $this->tmp_model->fkediting();
            
            //edita os dados
            $cod = $item[$name];
            if(!$this->tmp_model->editar($cod, $post)){
                //se o campo for do tipo required, gera a mensagem de erro
                if(array_key_exists('notnull', $array) && $array['notnull'] == true){
                    $this->setErrorMessage($this->tmp_model->getErrorMessage());
                    return false;
                }
            }
            $post[$name] = $cod;
        }
        return true;
    }
    
    public function apagar($post){
        $erro = "";
        foreach($this->fk as $name => $array){
            $id    = $post[$name];
            $model = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            if(!$this->tmp_model->apagar($id))
                $erro .= $this->tmp_model->getErrorMessage() . "<br/>";
        }
        if($erro != ""){
            $this->setErrorMessage($erro);
            return false;
        }
        return true;
    }
    
    public function selecionar($item, $array, $campo, $cod_item){
        $this->LoadModel($array['model'], "model");
        $var   = $this->model->getItem($item);
        return(empty ($var))?"":$var;
    }
    /*
    public function selecionar($item, $array, $campo, $cod_item){
        $this->LoadModel($array['model'], "model");
        $keys = $array['fkey']['keys'];
        
        $k1 = array_shift($keys);
        $k2 = array_shift($keys);
        $where = "`$k1` = '$item'";
        $var = $this->model->selecionar(array($k1, $k2), $where, 1);
        if(empty ($var)) return "";
        $var = array_shift($var);
        $out[$var[$k1]] = $var[$k2];
        return $out;
    }*/
}

?>
