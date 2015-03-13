<?php

namespace classes\Model;
class getItemModel extends \classes\Classes\Object{
    
    private $dados      = array();
    private $pkey       = '';
    private $cod_item   = '';
    private $campo      = '';
    private $refresh    = '';
    private $campos     = '';
    private $model      = '';
    private $model_name = '';
    private $tabela     = '';
    private function set($name, $value){
        $this->$name = $value;
        return $this;
    }
    
    public function setModel($obj){
        return $this->set('model', $obj);
    }
    
    public function setModelName($model_name){
        return $this->set('model_name', $model_name);
    }
    
    public function setPkey($pkey){
        if((is_array($this->campo) && empty($this->campo)) || 
           (!is_array($this->campo) && trim($this->campo) === "")){$this->setCampo($pkey);}
        return $this->set('pkey', $pkey);
    }
    
    public function setDados($dados){
        return $this->set('dados', $dados);
    }
    
    public function setTable($tabela){
        return $this->set('tabela', $tabela);
    }
    
    public function setCodItem($cod_item){
        return $this->set('cod_item', $this->model->antinjection($cod_item));
    }
    
    public function setCampo($campo){
        if(is_array($campo)){
            if(empty($campo)){$campo = $this->pkey;}
            foreach($campo as &$camp){
                $camp = trim($camp);
            }
        }
        elseif(trim($campo) === ""){$campo = $this->pkey;}
        return $this->set('campo', $campo);
    }
    
    public function setRefresh($refresh){
        return $this->set('refresh', $refresh);
    }
    
    public function setCampos($campos){
        return $this->set('campos', $campos);
    }
    
    /*
     * @Explication
     * seleciona dados
     * 
     * @args
     * @cod_item  o valor da chave primária
     *
     * @returns
     */
    public function getItem(){
        static $ret = array();
        $cached     = $this->getCachedItem($ret);
        if(!empty($cached)){return $cached;}
        
        $this->model->PrepareFk();
        $where    = $this->getItemWhere();
        $vars     = $this->model->selecionar($this->campos, $where, "1");
        
        if(empty ($vars)) {return $vars;}
        if(empty($this->campos)){$this->campos = array_keys($this->dados);}
        
        $out = $this->processItem($vars, $ret);
        //print_rh($out);
        return (count($out) == 1)?array_shift ($out):$out;
    }
    
            private function getCachedItem($ret){
                if($this->refresh || !empty($this->campos)){return array();}
                if(!array_key_exists($this->model_name, $ret)){return array();}
                if(!array_key_exists($this->cod_item, $ret[$this->model_name])){return array();}
                $this->db->clearJoin();
                return $ret[$this->model_name][$this->cod_item];
            }
            
            private function getItemWhere(){
                if(!is_array($this->campo)){return "`$this->tabela`.`$this->campo` = '$this->cod_item'";}
                for($i = 0 ; $i < count($this->campo); $i++){
                    $c = $this->campo[$i];
                    $j = isset($this->cod_item[$i])?$this->cod_item[$i]:$this->cod_item;
                    $where[] = "`$c` = '$j'";
                }
                return implode(" AND ", $where);
            }
                    
            private function processItem($vars, &$ret){
                $out = array();
                foreach($vars as $i => $var){
                    
                    $this->getItemVar($var);
                    $this->processItemCampo($ret, $var);
                    $this->prepareItemOut($i, $var,$out);
                    
                }
                return $out;
            }       
            
                    private function getItemVar(&$var){
                        foreach($this->campos as $name){
                            if(!isset($this->dados[$name])){continue;}
                            if(array_key_exists('title', $this->dados[$name])){
                                $this->item_title = $name;
                            }

                            if(array_key_exists("fkey", $this->dados[$name])){
                                $this->processItemFkey($this->dados[$name], $var, $name, $this->cod_item, $this->campo);
                            }elseif(array_key_exists("type", $this->dados[$name])){
                                $this->processItemType($this->dados[$name], $var, $name);
                            }
                        }
                    }
                    
                            private function processItemFkey($value, &$var, $name){
                                $this->LoadModel($value['fkey']['model'], "temp_model");
                                $card = "fk".$value['fkey']['cardinalidade'];
                                if($card == '1n' && !isset($var[$name])) {$var[$name] = $this->cod_item;}
                                if(isset($var[$name])){$var["__$name"] = $var[$name];}
                                $var[$name] = $this->model->$card->selecionar(
                                        isset($var[$name])?$var[$name]:"$name", 
                                        $value['fkey'], 
                                        $this->campo,
                                        $this->cod_item, 
                                        $this->model_name, 
                                        isset($value['fkey']['sort'])?$value['fkey']['sort']:""
                                );
                                //echo "<br/>$name <br/> ";print_r($var[$name]); echo "<br/>".$this->db->getSentenca() . "<br/> \n ".$this->$card->getErrorMessage() ."<br/><hr/>\n\n";
                                if($value['fkey']['cardinalidade'] == "nn" && is_array($var[$name])) {$var["__$name"] = array_keys ($var[$name]);}
                            }
                            
                            private function processItemType($value, &$var, $name){
                                if($value['type'] != "enum" || !isset($var[$name])) {return;}
                                $var["__$name"] = $var[$name]; 
                                $var[$name] = isset($value['options']) && isset($var[$name]) && isset($value['options'][$var[$name]])?
                                    $value['options'][$var[$name]]:"";
                            }
                    
                    private function processItemCampo(&$ret, $var){
                        if(is_array($this->campo) || !empty($this->campos)){return;}
                        if(!array_key_exists($this->campo, $var)){return;}
                        if(is_array($var[$this->campo])){return;}
                        $ret[$this->model_name][$var[$this->campo]] = $var;
                    }
                    
                    private function prepareItemOut($i, $var, &$out){
                        $dados = array_keys($this->dados);
                        foreach($dados as $name){
                            if(array_key_exists($name, $var)){
                                $out[$i][$name] = $var[$name];
                            }

                            if(array_key_exists("__$name", $var)) {
                                $out[$i]["__$name"] = $var["__$name"];
                            }
                        }
                    }
    
}