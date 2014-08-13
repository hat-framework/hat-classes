<?php

namespace classes\Model;
class FKnnModel extends FKModel{
       
    //trata dados das chaves estrangeiras
    public function inserir(&$post, $lastid, $pkey){
        $bool = true;

        //verifica se existe algum dado a ser inserido
        if(empty ($this->fk) || $post == "" || empty ($post)) return true;

        //varre as chaves estrangeiras n->n uma por uma
        foreach($this->fk as $name => $array){
            
            //verifica se existem dados a serem inseridos
            if(!array_key_exists($name, $post))continue;
            
            //inicializa as variaveis
            //$arr  = $post;
            $arr  = array();
            $key  = $this->getNameOfKeys($array['fkey']['model'], $this->ref);
            if(!is_array($key) || empty ($key)) return true;
            
            $name = str_replace(array("[", "]"), "", $name);
            $data = explode(",", $post[$name]);
            //carrega o model
            $this->LoadModel($array['fkey']['model'], "tmp_model");
            
            //insere os dados
            $arr[$key['src']] = trim($lastid);
            foreach($data as $valor){
                if($valor == "") continue;
                $arr[$key['dst']] = trim($valor);
                if(!$this->tmp_model->inserir($arr)){
                    //echo $this->tmp_model->db->getSentenca();
                    $this->setErrorMessage($this->tmp_model->getErrorMessage());
                    $bool = false;
                }
                /*print_r($arr);
                echo $this->tmp_model->db->getSentenca() . " <> ";
                echo $this->tmp_model->getErrorMessage();
                 */
            }
        }
        
        if(!$bool){
            $erro = $this->getErrorMessage();
            if($erro == ""){
                $erro = "Erro ao atualizar chaves do tipo NN, contate os administradores para mais detalhes.";
                $this->setErrorMessage($erro);
            }
        }
        return $bool;
    }
    
    public function editar(&$post, $lastid, $pkey, $id){
        $bool = true;
        //verifica se existe algum dado a ser inserido
        if(empty ($this->fk) || $post == "" || empty ($post)) return true;
        
        //varre as chaves estrangeiras n->n uma por uma
        foreach($this->fk as $name => $array){
            
            $name = str_replace(array("[", "]"), "", $name);
            $arr  = $post;
            if(is_array($pkey) && is_array($lastid))
                foreach($pkey as $c){
                    $v       = array_shift($lastid);
                    $arr[$c] = $v;
                }
            else{
                if(is_array($pkey)) {
                    $camp       = array_shift($pkey);
                    $arr[$camp] = $lastid;
                }
                else $arr[$camp]= $lastid;
            }
            
            if(!array_key_exists($name, $post) || !is_array($post[$name])) continue;

            $model = $array['fkey']['model'];
            $this->LoadModel($model, "tmp_model");
            foreach($post[$name] as $valor){
                $arr[$name] = $valor;
                if($id == "") $boolean = $this->tmp_model->inserir($arr);
                else          $boolean = $this->tmp_model->editar($id, $arr);
                if(!$boolean){
                    $this->setMessage($name, $this->tmp_model->getErrorMessage());
                    $bool = false;
                }
            }
        }
        return $bool;
    }
    
    
    public function apagar($id){

    }
    
    public function selecionar($item, $array, $campo, $cod_item, $model_origem, $orderby){
        
        //carrega o model da chave estrangeira
        $this->LoadModel($array['model'], "model");
        $table_dst = $this->model->getTable();
        
        //salva em $sname o nome da chave estrangeira
        //que referencia o model de origem
        $sname = "";
        $dados = $this->model->getDados();
        $selecionar = array("$table_dst.*");
        $break = false;
        foreach($dados as $name => $arr){
            if(!$break){
                if(!array_key_exists("fkey", $arr) ||
                   $arr['fkey']['cardinalidade'] != "1n") continue;

                if($arr['fkey']['model'] == $model_origem){
                    $sname = $name;
                    $break = true;
                }
            }
        }
        if($sname == "") throw new \classes\Exceptions\modelException (__CLASS__. "::$model_origem -- " .__LINE__ , "O nome da chave estrangeira
            referenciada pelo modelo é inválido ou indefinido");
        
        $wh = (array_key_exists('filther', $array))?$array['filther']:"";
        foreach($dados as $name => $arr){
            
            //se nao for chave estrangeira,
            //se o model da chave for o mesmo que está chamando
            //se a cardinalidade não é n para n, continua
            if(!array_key_exists("fkey", $arr) ||
               $arr['fkey']['cardinalidade'] != "1n") continue;

            if($arr['fkey']['model'] == $model_origem){
                $model_origem = "";
                continue;
            }
            
            //carrega os dados do model apontado pela chave estrangeira da tabela n para n
            $this->LoadModel($arr['fkey']['model'], 'model_src');
            $table_src = $this->model_src->getTable();
            $name_dst  = $this->model_src->getPkey();
            if(is_array($name_dst)) $name_dst = array_shift($name_dst);
            
            
            $dados_src    = $this->model_src->getDados();
            foreach($dados_src as $name_camp => $array_camp){
                
                //se coluna não é para ser exibida
                if(!array_key_exists("display", $array_camp) || !$array_camp['display']) continue;
                
                //se for uma chave estrangeira do tipo n1 ou nn, não exibe
                if(array_key_exists("fkey", $array_camp) && (
                   $array_camp["fkey"]['cardinalidade'] == 'n1' ||
                   $array_camp["fkey"]['cardinalidade'] == 'nn')
                ) continue;
                
                //adiciona a lista de itens a serem exibidos
                $selecionar[] = "$table_src.$name_camp"; 
            }
            
            //faz o join das tabelas
            $this->model->db->Join($table_dst, $table_src, array($name), array($name_dst), "LEFT");
            $ref   = $arr['fkey']['keys'];
            $k1    = array_shift($ref);
            
            $where = "`$table_dst`.`$sname` = '$cod_item'";
            $wh    = ($wh == "")?$where:"$wh AND ($where)";
            
            //seleciona o item do banco de dados
            //$var   = $this->model->paginate(0, "", "", "", 1000, $selecionar, $where, $orderby);
            $var   = $this->model->selecionar($selecionar, $where, "", "", $orderby);
            //print_r($var); echo "<br/>"; print_r($arr['fkey']); echo "<br/>".$this->model->db->getSentenca() . "<br/>";
            //se for vazio retorna vazio
            if(empty ($var)) return "";

            //relaciona as chaves estrangeiras em mapas
            $out = array();
            foreach($var as $value)
                $out[$value[$k1]] = $value;
            
            //retorna os mapas com as chaves
            return $out;
            
        }
        
        //if($sname == "") 
        throw new classes\Exceptions\modelException(
                "Não existe nenhum atributo 1:n no model auxiliar"
        );
    }
    
    public function getNameOfKeys($model_src, $model_dst){

        //inicializa a variavel
        $return    = array();
        
        //recupera a chave primária da tabela destino
        $this->LoadModel($model_dst, "tmp_obj");
        $key_atual = $this->tmp_obj->getPkey();
        
        //recupera todas as colunas da tabela fonte
        $this->LoadModel($model_src, "t_model");
        $dados = $this->t_model->getDados();
        
        foreach($dados as $tempname => $arr){
            
            //se não for um dado do tipo n => n, procura o próximo
            if(!array_key_exists("fkey", $arr) || $arr['fkey']['cardinalidade'] != "1n")continue;
            
            //associa as chaves estrangeiras fonte e destino
            if(!array_key_exists('keys', $arr['fkey'])){
                if(empty ($return)) $return = false;
                continue;
            }
            
            $achou = false;
            foreach($arr['fkey']['keys'] as $ref){
                if($ref == $key_atual){
                    $achou = true;
                    $return['src'] = $tempname;
                }
            }
            if(!$achou) $return['dst'] = $tempname;
            if(count($return) == 2) break;
        }
        return $return;
    }
}

?>