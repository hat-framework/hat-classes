<?php

namespace classes\Model;
use classes\Classes\session;
use classes\Classes\Object;
class Model extends Object
{
    //usado nas operacoes de inserir/editar
    protected $tabela;
    protected $pkey;
    protected $id = "";
    protected $dados = array();
    protected $post;
    protected $lastid;
    
    protected $model_label       = "";
    protected $model_description = "";
    
    //models auxiliadres
    protected $disabled_index = false;
    protected $fkedit = false;
    protected $feature  = "";
    protected $model_name;
    protected $cache = true;
    public $fk11;
    public $fkn1;
    public $fknn;
    public $fkmodel;
    
    public function  __construct() {
        $this->fkmodel   = new FKModel();
        $this->fk11      = new FK11Model();
        $this->fk1n      = new FK1nModel();
        $this->fkn1      = new FKn1Model();
        $this->fknn      = new FKnnModel();
        $this->LoadResource("database", "db", false);
    }
    
    public function setRestriction($camp, $value, $op = '='){
        if($camp === ""){return;}
        if(!isset($this->dados[$camp])){return;}
        if(!isset($this->resticted_where[$camp])){$this->resticted_where[$camp] = array();}
        $this->resticted_where[$camp][] = array($value, $op);
    }
    
    public function restoreSession($item){
        if(!is_array($item) || empty($item)) {return;}
        
        $this->PrepareFk();
        $keys = $this->fkmodel->get1n();
        
        foreach($keys as $name => $k){
            $model = $k['fkey']['model'];
            if($model == LINK) continue; //para o caso de categoria que aponta para subcategorias
            if(!isset($item["__".$name])){continue;}
            $_SESSION[$model] = $item["__".$name];
            if(isset($item[$name])) {$_SESSION["{$model}_title"] = $item[$name];}
        }
    }
    
    public function setModelName($model) {
        static $instances = array();
        $this->model_name = $model;
        //echo $this->model_name . "<br/>";
        if(!isset($instances[$model])){
            $this->LoadData($model, 'dm', false);
            if($this->dm == null) {return array();}
            $instances[$model] = $this->dm;
        }else {$this->dm = $instances[$model];}
        $this->dados = $this->dm->getDados($model);
        
    }
    
    public function getModelName() {
        return $this->model_name;
    }
    
    public function dropTable(){
        $this->db->deleteTable($this->table);
    }
    
    /*
     * @Explication
     * seleciona dados
     * 
     * @args
     *
     * @returns
     */
    public function selecionar($campos = array(), $where = "", $limit = "", $offset = "", $orderby = ""){
        static $sentencas = array();
        if(!$this->cache){
            $cachevar = serialize($campos).$where. $limit .$offset.$orderby;
            if(array_key_exists($cachevar, $sentencas)) {
                $this->db->clearJoin();
                return $sentencas[$cachevar];
            }
        }
        if($orderby == "" && (array_key_exists("ordem", $this->dados))){
            $orderby = "$this->tabela.ordem ASC";
        }
        $wh = $this->getRestrictions($where);        
        $var= $this->db->Read($this->tabela, $campos, $wh, $limit, $offset, $orderby);
        if(!$this->cache) {$sentencas[$cachevar] = $var;}
        //echo $this->db->getSentenca();
        return $var;
    }
    
            private function getRestrictions($where){
                if(!isset($this->resticted_where) || empty($this->resticted_where)){return $where;}
                $restwh = array();
                foreach($this->resticted_where as $camp => $arr){
                    foreach($arr as $data){
                        $restwh[] = "$camp {$data[1]} {$data[0]}";
                    }
                }
                $wh = implode(" AND ", $restwh);
                return ($where === "")?$wh:"$wh AND ($where)";
            }
    
    protected $itens = array();
    public function getField($cod, $field, $camp = ""){
        if(!array_key_exists($field, $this->dados)) { return '';}
        $where = $this->genWhere($camp, $this->pkey, $cod);
        $v = $this->selecionar(array($field), $where);
        //echo $this->db->getSentenca();
        if(empty($v)) { return '';}
        if(count($v) > 1) {
            $out = array();
            foreach($v as $a) {$out[] = $a[$field];}
            return $out;
        }
        return $v[0][$field];
    }
    
    protected function setField($cod, $field, $valor){
        if(!array_key_exists($field, $this->dados)) { 
            $this->setErrorMessage("O campo $field não existe");
            return false;
        }
        return $this->editar($cod, array($field => $valor));
    }
    
    public function disableCache(){
        $this->cache = false;
    }
    
    public function enableCache(){
        $this->cache = true;
    }
    
    public function getAltenticationRow(){
        foreach($this->dados as $name => $arr){
            if(array_key_exists('autentication', $arr)){
                return $name;
            }
        }
        return "";
    }
    
    public function getCategorizedRow(){
        foreach($this->dados as $name => $arr){
            if(array_key_exists('categorize', $arr)) return array('name' => $name, 'fkey' => $arr['fkey']);
        }
        return "";
    }
    
    public function getSublist($page, $campo, $link, $cod_pkey = ""){
        if(!array_key_exists($campo, $this->dados)) {return false;}
        if(!array_key_exists('fkey', $this->dados[$campo])) {return false;}
        extract($this->dados[$campo]['fkey']);
        if($cardinalidade != 'n1') {return false;}
        $this->LoadModel($model, 'md');
        $qtd     = isset($limit)? $limit:"10";
        $filther = isset($filther)?$filther:'';
        
        $where = $this->getSublistWhere($cod_pkey, $filther);
        return $this->md->paginate($page, $link, "", "", $qtd, array(), $where);
    }
    
            private function getSublistWhere($cod_pkey, $filther){
                $where = $this->getSublistWherePkey($cod_pkey);
                if(isset($filther) && $filther != ""){
                    $where = ($where != "")?"$where AND ($filther)":"$filther";
                }
                return $where;
            }
            
                   private function getSublistWherePkey($cod_pkey){
                        if($cod_pkey == ""){return "";}
                        $dados = $this->md->getDados();
                        foreach($dados as $nm => $d){
                            if(!isset($d['fkey'])) {continue;}
                            if($d['fkey']['model'] != $this->model_name){continue;}
                            return ("$nm = '$cod_pkey'");
                        }
                   }
    
    public function paginate($page, $link = "", $cod_item = "", $campo = "", $qtd =20, $campos = array(), $adwhere = "", $order = ""){
        $this->LoadResource("html/paginator", 'pag');
        $this->LoadResource("html", 'html');
        $this->pag->startDebug();

        $where  = $this->getPaginateWhere($cod_item, $campo, $adwhere);
        $lk     = ($link == "")? CURRENT_MODULE . "/" . CURRENT_CONTROLLER."/show/" : $link;
        $cps    = $this->getCampos($campos);
        $ordem  = $order;
        $this->pag->setJoin($this->db->getJoin());
        $this->pag->setWhere($where);
        $this->pag->Paginacao($this->tabela, $lk, $qtd, $page);
        
        $pk = is_array($this->pkey)?implode(", ", $this->pkey):$this->pkey;
        if($ordem == "") {$ordem = array_key_exists("ordem", $this->dados)?"ordem DESC":"$this->tabela.$pk DESC";}
        return $this->pag->selecionar($this, $cps, $where, $ordem);
    }
    
            private function getPaginateWhere($cod_item, $campo, $adwhere){
                $where = ($cod_item == "")?$cod_item : $this->genWhere($campo, $this->pkey, $cod_item);
                if($where == ""){return $adwhere;}
                return (trim($adwhere) != "")?" $where AND( $adwhere ) ":$adwhere;
            }
    
    public function getPaginationCount($adwhere = "", $cod_item = "", $campo = ""){
        $this->LoadResource("html/paginator", 'pag');
        $this->LoadResource("html", 'html');

        $where = ($cod_item == "")?$cod_item : $this->genWhere($campo, $this->pkey, $cod_item);
        $where .= $adwhere;
        $this->pag->setJoin($this->db->getJoin());
        $this->pag->setWhere($where);
        $this->pag->Paginacao($this->tabela, LINK, 10, 0);
        
        return $this->pag->getLastCount();
    }
    
    private $item_title = "";
    public function getItemTitle($item){
        if($this->item_title == ""){
            $nm = 'title/'.$this->model_name;
            $v  = session::getVar($nm);
            $this->item_title = ($v === "")?$this->getTitleInArray($nm):$v;
        }
        return (isset($item[$this->item_title]))?$item[$this->item_title]:'';
    }
    
            private function getTitleInArray($nm){
                $title = "";
                foreach($this->dados as $name => $value){
                    if(!array_key_exists('title', $value)){continue;}
                    $title = $name;
                    session::setVar($nm, $name);
                    break;
                }
                return $title;
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
    public function getItem($cod_item, $campo = "", $refresh = false, $campos = array()){
        static $gim = null;
        if($gim === null){$gim = new getItemModel();}
        return $gim->setModel($this)
            ->setModelName($this->model_name)
            ->setDados($this->dados)
            ->setPkey($this->pkey)
            ->setTable($this->tabela)
            ->setCodItem($cod_item)
            ->setCampo($campo)
            ->setRefresh($refresh)
            ->setCampos($campos)
            ->getItem();
    }

    public function getSimpleItem($cod_item, $campos = array(), $coluna = ""){
        $pk = $this->pkey;
        $camps  = $this->getCampos($campos); 
        $where  = $this->genWhere($coluna, $pk, $cod_item);
        $var    = $this->selecionar($camps,$where, "1");
        //print_r($var);echo $this->db->getSentenca() . "<hr/>";
        return (count($var) === 1)?array_shift($var):$var;
    }
    
    public function getPlainItem($key){
        $w = $this->getPlainItemWhere($key);
        $v = $this->selecionar(array(), $w, 1);
        return(empty($v))?$v:array_shift($v);
    }
    
            private function getPlainItemWhere($key){
                if(!is_array($this->pkey)){return "$this->pkey = '".$this->antinjection($key)."'";}
                $w = "";
                foreach($this->pkey as $pk){
                    $k  = array_shift($key);
                    $w .= "$pk = '".$this->antinjection($k)."'";
                }
                return $w;
            }

    public function listSimpleItem($cod_item, $campos = array(), $coluna = ""){
        $pk    = $this->pkey;
        $camps = $this->getCampos($campos);
        $where = $this->genWhere("$this->tabela`.`$coluna", $pk, $cod_item);
        return $this->selecionar($camps,$where);
    }
            
    public function getCampos($campos = array()){
        $ignore_display = false;
        $camps = $this->getCamposData($campos, $ignore_display);
        $join  = $this->db->getJoin();
        $find  = $this->getCamposFounded($camps, $ignore_display);
        $this->db->addJoin($join);
        return $find;
    }
    
            private $sql_function = array("COUNT",'SUM');
            private function getCamposData($campos, &$ignore_display){
                if(empty($campos)){return $this->dados;}
                $tmp_camps = array();
                foreach($campos as $camp){
                    if(array_key_exists($camp, $this->dados)){$tmp_camps[$camp] = $this->dados[$camp];}
                    if(false !== strposa($camp, $this->sql_function)){
                        $tmp_camps[$camp] = array('val' => $camp, 'type'=>'val');
                    }
                    continue;
                }
                $ignore_display = true;
                return $tmp_camps;
            }
            
            private function getCamposFounded($campos, $ignore_display){
                $this->db->setJoin("");
                $used = array($this->tabela => 0);
                $find = array();
                foreach($campos as $name => $data){
                    if(isset($data['type']) && $data['type'] === 'val' && isset($data['val'])){
                        $find[] = $data['val'];
                        continue;
                    }
                    if(!$ignore_display && (!array_key_exists("display", $data) || !$data['display'])){continue;}
                    if(!array_key_exists('fkey', $data)) {
                        $find[] = "$this->tabela.$name"; 
                        continue;
                    }
                    
                    $f = $this->getCamposFkey($data, $name,$used);
                    $this->addToFounded($f, $find);
                }
                return $find;
            }
            
            
                    private function getCamposFkey($data, $name, &$used){
                        extract($data['fkey']);
                        $cardinalidade = $data['fkey']['cardinalidade'];
                        $model         = $data['fkey']['model'];
                        $keys          = $data['fkey']['keys'];
                        if(in_array($cardinalidade, array('nn','n1'))) {return '';}
                        if($cardinalidade == '11'){return "$this->tabela.$name";}

                        //cardinalidade 1n
                        $this->LoadModel($model, 'fktmp_model');
                        $tab     = $this->fktmp_model->getTable();
                        $tabname = $tab;
                        if(isset($used[$tab])){
                            $used[$tab]++;
                            $tabname = "{$tab}_". $used[$tab];
                            $tab = "$tab as $tabname" ;
                        }else {$used[$tab] = 0;}
                        return $this->getCamposFkeyFounded($keys,$tabname, $tab, $name);
                    }
                    
                            private function getCamposFkeyFounded($keys,$tabname, $tab, $name){
                                $find = array();
                                $k1   = array_shift($keys);
                                if($k1 !== null) {
                                    $this->db->join($this->tabela, $tab, array($name), array($k1), "LEFT");
                                    $find[] = "$tabname.".$k1. " AS __$name";
                                }
                                
                                $k2   = array_shift($keys);
                                if($k2 !== null) {
                                    $find[] = "$tabname.".$k2. " AS $name";
                                }
                                if(empty($keys)){return $find;}    
                                
                                $dados = $this->fktmp_model->getDados();
                                foreach($keys as $key){
                                    if(!array_key_exists($key, $dados)){continue;}
                                    $keyname = isset($dados[$key]['name'])?$dados[$key]['name']:"$name $key";
                                    $find[] = "$tabname.".$key. " AS `$keyname`";
                                }
                                
                                return $find;
                            }
                    
                    private function addToFounded($f, &$find){
                        if(!is_array($f)){$f = array($f);}
                        foreach($f as $ff){
                            if(trim($ff) === ''){return;}
                            $find[] = $ff;
                        }
                    }


    /*
     * @Explication
     * Insere valores no banco de dados
     * 
     * @args
     * $id   o valor da chave primária
     * $post os valores a serem alterados
     *
     * @returns
     * @boolean: true caso consiga apagar, false caso contrario
     */
    public function inserir($dados){

        //validacao dos dados
        $this->setMessage("is_editing", '0');
        $this->post = $dados;
        if(!$this->validate()){return false;}
        
        //insere chaves estrangeiras do tipo 1-1
        $this->PrepareFk();
        if(!$this->fk11->inserir($this->post, $dados)){
            $this->setErrorMessage($this->fk11->getErrorMessage());
            return false;
        }

        //associa os dados
        if(!$this->associa()) {return false;}

        //insere os dados
        $this->lastid = $this->db->Insert($this->tabela, $this->post);
        if(!$this->InsertionProcessLastId($dados)){return false;}
        
        return $this->setSuccessMessage("Dados Inseridos Corretamente!");
    }
    
            private function InsertionProcessLastId($dados){
                if(is_array($this->pkey)){
                    $this->lastid = array();
                    foreach($this->pkey as $pk){
                        $this->lastid[] = $this->post[$pk];
                    }
                }

                if($this->lastid === false){
                    $erro = $this->db->getErrorMessage();
                    if($erro == ""){
                        $e = (DEBUG)?'Debug: '.$this->db->getSentenca():"";
                        $erro = "Erro desconhecido ao inserir dados. ". $e;
                    }
                    return $this->setErrorMessage($erro);
                }
                
                //insere os dados das chaves estrangeiras n -> n
                if(!$this->fknn->inserir($dados, $this->lastid, $this->pkey)){$this->setAlertMessage($this->fknn->getErrorMessage());}
                return true;
            }
    
    public function indexar(){
        $this->LoadModel('search/index', 'sindex');
        $this->sindex->indexar($this->model_name, $this->dados, $this->pkey, $dados);
    }
    
    /*
     * @Explication
     * Edita valores no banco de dados
     * 
     * @args
     * $id   o valor da chave primária
     * $post os valores a serem alterados
     *
     * @returns
     * @boolean: true caso consiga apagar, false caso contrario
     */
    public function editar($id, $post, $camp = ""){
        
        //validacao dos dados
        $this->setMessage("is_editing", '1');
        $this->post = $post;
        $this->id   = $id;
        if(!$this->validate())   {return false;}
        if(!$this->associa(true)){return false;}

        //atualiza o banco de dados
        $where = $this->genWhere($camp, $this->pkey, $id);
        if(!$this->db->Update($this->tabela,$this->post, $where)){
            $this->setErrorMessage($this->db->getErrorMessage());
            return false;
        }
        
        //edita chaves estrangeiras do tipo 1-1
        $this->PrepareFk();
        if(!$this->fk11->editar($post, $id) && $this->fkedit == false){
            $this->setAlertMessage($this->fk11->getErrorMessage());
            return false;
        }

        //seta mensagem de sucesso
        return $this->setSuccessMessage("Dados Alterados Corretamente!");
    }
    
    /**
     * Atenção ao utilizar esta função, ela não deveria ser pública, está assim só para o
     * caso de listar as chaves estrangeiras N1
     */    
    public function genWhere($campo, $pkey, $id){
        $camp  = ($campo == "")? $pkey : $campo ;
        if(!is_array($camp) || !is_array($id)){
            if(is_array($camp)) {
                return $this->genWhereCampCase($camp, $id);
            }
            if(is_array($id)) {
                return $this->genWhereIdCase($id, $camp);
            }
            return "`$camp` = '$id'";
        }
        
        return $this->genWhereDualArray($camp, $pkey, $id);
    }
    
            private function genWhereDualArray($camp, $pkey, $id){
                if(!is_array($camp) || is_array($id)){return "";}
                $prefix = ($camp == $pkey)?"`$this->tabela`.":"";
                $and    = "";
                $where  = "";
                $i      = 0;
                foreach($camp as $c){
                    $v = $id[$i++];
                    if(strstr($c, ".")){$c = str_replace(".", "`.`", $c);}
                    $where .= "$and $prefix`$c` = '$v'";
                    $and = " AND ";
                }
                return $where;
            }
            
            private function genWhereCampCase($camp, $id){
                if(!is_array($camp)) {return "";}
                reset($camp);
                $campo   = current($camp);
                return "`$campo` = '$id'";
            }
            
            private function genWhereIdCase($id, $camp){
                reset($id);
                $where   = "";
                $or      = "";
                $campo   = $camp;
                foreach($id as $i){
                    $where  .= "$or `$campo` = '$i'";
                    $or = " OR ";
                }
                return $where;
            }

    /*
     * @Explication
     * Apaga um item do banco de dados
     * 
     * @args
     * $id o valor da chave primária
     *
     * @returns
     * @boolean: true caso consiga apagar, false caso contrario
     */
    
    public function apagar($valor, $chave = ""){
        
        //se não foi enviada uma chave, assume a chave estrangeira
        $this->PrepareFk();
        if($chave == "") {$chave = $this->pkey;}
        
        $wh = $this->apagarGetWhere($valor, $chave);
        if($wh === false){return false;}
        
        //apaga o item
        if(!$this->db->Delete($this->tabela, $wh)){
            $this->setErrorMessage($this->db->getErrorMessage());
            return false;
        }
        
        return $this->setSuccessMessage("Conteudo apagado com sucesso!");
    }
    
            private function apagarGetWhere($chave, $valor){
                
                if(!is_array($chave)){
                    $valor = $this->antinjection($valor);
                    $chave = $this->antinjection($chave);
                    return "`$chave` = '$valor'";
                }
                
                //seta o where composto
                $c1 = count($valor);
                if(count($chave) != $c1){
                    $this->setErrorMessage("A chave e o valor devem conter o mesmo número de elementos");
                    return false;
                }

                $arr = array();
                while($c1-- > 0){
                    //prepara os valores a serem consultados
                    $valor[$c1] = $this->antinjection($valor[$c1]);
                    $chave[$c1] = $this->antinjection($chave[$c1]);
                    $arr[] = "`$chave[$c1]` = '$valor[$c1]'";
                }
                return implode(" AND ", $arr);
            }
    
    public function PrepareFk(){
        $this->fkmodel->analiza($this->dados);
        $this->fk11->set($this->fkmodel->get11(), $this->model_name);
        $this->fkn1->set($this->fkmodel->getn1(), $this->model_name);
        $this->fknn->set($this->fkmodel->getnn(), $this->model_name);
    }
    
    /*
     * @Explication
     * Valida a classe de acordo com algumas regras definidas na variavel dados
     *
     * @returns
     * @boolean: true caso consiga validar, false caso contrario
     */
    protected function validate(){
        if(false === $this->validadeEmptyPost()){return false;}
        $this->validateUnsetPkey();
        $this->post = $this->validateGetPost();
        return $this->validateValidator();
    }
    
            private function validadeEmptyPost(){
                if(!empty ($this->post)){return true;}
                return $this->setErrorMessage("Dados a serem inseridos estão vazios!");
            }
            
            private $invalid_pkeys = array(0,'0','');
            private function validateUnsetPkey(){
                if(is_array($this->pkey)){return;}
                if(!isset($this->post[$this->pkey])){return;}
                if(!in_array($this->post[$this->pkey], $this->invalid_pkeys)){return;}
                unset($this->post[$this->pkey]);
            }
            
            private function validateGetPost(){
                $post = array();
                $this->dados = $this->getDados();
                foreach($this->post as $name => $valor){
                    if(!array_key_exists($name, $this->dados)){continue;}
                    if(!is_array($valor)){
                        $pos = strpos($valor, "FUNC_");
                        $post[$name] = ($pos === false)?$this->antinjection($valor):$valor;
                        continue;
                    }

                    foreach($valor as $i => $v){
                        if(is_object($v) || is_array($v))continue;
                        $pos = strpos($v, "FUNC_");
                        $post[$name][$i] = ($pos === false)?$this->antinjection($v):$v;
                    }
                }
                if(array_key_exists('antispam', $this->post)) {$post['antispam'] = $this->post['antispam'];}
                return $post;
            }
            
            private function validateValidator(){
                $this->LoadResource("formulario/validator", "pval");
                if(!$this->pval->validate($this->dados, $this->post)){
                    $this->setSimpleMessage('validation', $this->pval->getMessages());
                    $e    = $this->getMessages();
                    $erro = (isset($e['validation']['erro'])?$e['validation']['erro']: "Erro ao validar os dados a serem inseridos");
                    $this->setErrorMessage($erro);
                    return false;
                }
                $this->post = $this->pval->getValidPost();
                return true;
            }
    
    private function debugPost($method = "", $line = ''){
        if($this->tabela == "usuario") return;
        echo "\n\n$this->tabela - $method - $line<br/>";
        print_r($this->post);
        echo "\n\n<br/>";
    }


    /*
     * @Explication
     * Associa os dados enviados para a classe
     *
     * @returns
     * @boolean: true caso consiga associar, false caso contrario
     */
    protected final function associa($edit = false){
        
        if(!$this->checkDataForAssociation()){return false;}       
        $post = $this->preparePostAssociation();
        $this->processEmptyAssociationId($post);
        if(!$this->processEmptyAssociationPost($post)){return false;}
        $this->post = $post;
       // print_r($this->post); die();
        return true;
    }
            private function checkDataForAssociation(){
                if(!is_array($this->dados) || empty ($this->dados)){
                    $this->setErrorMessage("Erro no sistema! Dados a serem inseridos não foram configurados,
                        consulte o administrador");
                    return false;
                }
                return true;
            }
    
            private function preparePostAssociation(){
                $post = array();        
                foreach($this->dados as $tname => $arr){

                    if(!array_key_exists($tname, $this->post)){continue;}
                    //associa as chaves estrangeiras
                    if(!array_key_exists('fkey', $arr) || 
                        $arr['fkey']['cardinalidade'] == '11' ||
                        $arr['fkey']['cardinalidade'] == '1n'){
                        $post[$tname] = $this->post[$tname];
                    }

                }
                return $post;
            }

            private function processEmptyAssociationId(&$post){
                if($this->id != ""){return;}
                $pkey = is_array($this->pkey)?$this->pkey:array($this->pkey);
                foreach($pkey as $pk){
                    if(!array_key_exists($pk, $post)){continue;}
                    if(!array_key_exists($pk, $this->dados)){continue;}
                    if(!array_key_exists('ai', $this->dados[$pk])){continue;}
                    $post[$pk] = "FUNC_NULL";
                }
            }

            private function processEmptyAssociationPost($post){
                if(!empty ($post)){return true;}
                $model = @ucfirst(end(explode("/", $this->model_name)));
                $this->setErrorMessage("Caro usuário, os dados do formulário enviado não foram preenchidos no módulo: $model!");
                return false;
            }
    
    
    public function getLastId(){
        if($this->lastid != ""){ return $this->lastid;}
        $pkey  = $this->pkey;
        $order = "";
        $this->prepareLastIdPkey($pkey, $order);
        
    	$var = $this->selecionar($pkey, "", "1", "", "$order DESC");
        if(empty ($var)) {return "";}
        
    	$v = array_shift($var);
        foreach($v as $arr) {$out[] = $arr;}
        return(count($out) == 1)?array_shift($out):($out);
    }
    
            private function prepareLastIdPkey(&$pkey, &$order){
                
                $order = "";
                if(!is_array($pkey)){
                    $order = $pkey;
                    $pkey = array($this->pkey);
                    return;
                }
                
                $virg  = "";
                foreach ($pkey as $pk){
                    $order .= "$virg $pk";
                    $virg   =  ",";
                }
                
            }
    
    /*
     * @Explication
     * Retorna o nome da chave primaria da tabela
     *
     * @returns
     * @string: nome da chave primaria
     */
    public function getPkey(){
        return $this->pkey;
    }
    
    /*
     * @Explication
     * Retorna o nome da chave que entitula o modelo
     *
     * @returns
     * @string: nome da coluna com o título a ser exibido para o usuário
     */
    public function getModelTitle(){
        $title = "";
        foreach($this->dados as $name => $dado){
            if(array_key_exists('title', $dado) && $dado['title'] === true){
                return $name;
            }
            if($title === "" && $dado['type'] === 'varchar'){$title = $name;}
        }
        return($title === "")?$this->getPkey():$title;
    }
    
    /*
     * @Explication
     * Retorna os nomes das chaves que podem ser buscadas na tabela
     */
    public function getModelSearchableKeys(){
        $searchable=array();
        foreach($this->dados as $name => $dado){
            if(isset($dado['private']) && $dado['private'] === true){continue;}
            if(
               (array_key_exists('title', $dado) && $dado['title'] === true) || 
               (array_key_exists('type', $dado)  && $dado['type'] === 'varchar')
            ){
                $searchable[$name] = $name;
            }
        }
        return(array_keys($searchable));
    }
    
    /*
     * @Explication
     * Retorna o nome da tabela do banco de dados
     *
     * @returns
     * @string: nome da tabela
     */
    public function getTable(){
    	return $this->tabela;
    }
    
    public function getModelLabel(){
    	return $this->model_label;
    }
    
    public function getModelDescription(){
    	return $this->model_description;
    }
    
    public function blockBecauseFeature(){
        if($this->feature !== ""){
            if(!defined($this->feature)){return true;}
            $constant = constant($this->feature);
            if($constant !== true){return true;}
        }
        return false;
    }
        /*
     * @Explication
     * Retorna o nome da tabela do banco de dados
     *
     * @returns
     * @string: nome da tabela
     */
    public function getDados(){
        return $this->blockBecauseFeature()?array():$this->dados;
    }
    
    public function getCount($where = ''){
        $join = $this->db->getJoin();
        $where = (trim($where) == '')?'':"WHERE $where";
    	$var = $this->db->ExecuteQuery("SELECT COUNT(*) as total FROM $this->tabela $join $where");
        $this->db->clearJoin();
    	return $var[0]['total'];
    }
    
    public function fkediting(){
        $this->fkedit = true;
    }
    
    public function antinjection($sql, $permSimpleTags = true){
        if(!$permSimpleTags){
            $sql = strip_tags($sql, "<p><br/><span><img><a>");
            return str_replace(array("'"), array('"'), $sql);
        }
        if(!is_array($sql)){return $this->tratamento($sql);}
        foreach($sql as &$sq){
            if(is_array($sq)) {continue;}
            $sq = $this->tratamento($sq);
        }
        return $sql;
    }
    
    private function tratamento($sql){
        return trim(str_replace(
                array("'", '<?'   , '?>'   , '<script'  , '</script'   , '<style'  , '</style'), 
                array('"', '&lt;?', '?&gt;','&lt;script', '&lt;/script','&lt;style', '&lt;/style'), 
                $sql)
        );
    }
    
    public function ExecuteService($serviceName, $params = array()){
        return $this->LoadClassFromPlugin("$this->model_name/services/{$serviceName}Service", 'svc')->execute($params);
    }
    
    public function join($modeldst, $key_src = "", $key_dst = "", $join = 'LEFT', $modelsrc = ''){
        $tb   = $this->LoadModel($modeldst, 'tmp_model')->getTable();
        $ksrc = ($key_src == "")?$this->pkey:$key_src;
        $kdst = $this->getKeyDst($key_dst);
        if(!is_array($ksrc)){$ksrc = array($ksrc);}
        if(!is_array($kdst)){$kdst = array($kdst);}
        $tabela = ($modelsrc === "")?$this->tabela:$this->LoadModel($modelsrc, 'tmp_model')->getTable();
        $this->db->Join($tabela, $tb, $ksrc, $kdst, $join);
        return $tb;
    }
    
            private function getKeyDst($kdst){
                if($kdst !== ""){return $kdst;}
                $dados = $this->tmp_model->getDados();
                foreach($dados as $key => $var){
                    if(!isset($var['fkey'])) continue;
                    if($var['fkey']['model'] != $this->model_name || !in_array($var['fkey']['model'], array('11', '1n'))) continue;
                    return $key;
                }
                
            }
    
    public function importDataFromArray($dados, $insertIgnore = false){
        $callback = $this->getImportationCallback();
        $cbkdata  = $this->getImportationCallbackData();
        if(false === $this->db->importDataFromArray($dados, $this->tabela, $callback, $insertIgnore, $cbkdata)){
            return $this->setErrorMessage($this->db->getErrorMessage());
        }
        return true;
    }
    
    protected function getImportationCallback(){
        return null;
    }
    
    protected function getImportationCallbackData(){
        return array();
    }


    protected function listImplode($array, $glue = "','"){
        if(!is_array($array)){return $array;}
        return implode("','", $array);
    }
    
      /**
     * Gera codigo em base64 dentro do array, e já insere dentro do array se desejar
     * @param array $arr
     * @param array $generator Ex: $generator = array(1,2,3,6)
     * @param mixed $array_unshift . True retorna o array com o codigo no inicio, False retorna apenas o código, String retorna o array com o código na chave $array_unshift
     * @return mixed array or string
     */
   public function genKey($arr,$generator, $array_unshift = false){
       if(!is_array($arr)){return $arr;}
       if(!is_array($generator)){return "";}
       $str = '';
       foreach($generator as $gen){
           if(!array_key_exists($gen, $arr)){continue;}
           $str.= $arr[$gen].'-';
       }
       $cod = trim(chunk_split(base64_encode($str)));
       if($array_unshift !== false){
           if($array_unshift === true){array_unshift($arr, $cod);}
           else{$arr[$array_unshift] = $cod;}
           return $arr;
       }
       return $cod;
    }
    
    
    public function listSide($value, $page = 0, $qtd = '10', $campos = array(), $adwhere = '', $order = ''){
        if(!is_array($this->pkey)){
            throw new \classes\Exceptions\InvalidArgumentException("Apenas modelos com chaves primárias duplas podem acessar este método!");
        }
        
        $key = $this->pkey[0];
        $where = "$key='$value'";
        if($adwhere !== ""){$where .= " AND ($adwhere)";}
        return $this->paginate($page, '', '', '', $qtd, $campos, $where, $order);
    }
    
}