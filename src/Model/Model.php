<?php

namespace classes\Model;
use classes\Classes\cookie;
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
    protected $fk11;
    protected $fkn1;
    protected $fknn;
    protected $fkmodel;
    protected $disabled_index = false;
    protected $fkedit = false;
    protected $feature  = "";
    protected $model_name;
    protected $cache = true;
    
    public function  __construct() {
        $this->fkmodel   = new FKModel();
        $this->fk11      = new FK11Model();
        $this->fk1n      = new FK1nModel();
        $this->fkn1      = new FKn1Model();
        $this->fknn      = new FKnnModel();
        $this->LoadResource("database", "db", false);
    }
    
    public function  restrictByAutor($camp, $value){
        if(!isset($this->dados[$camp])){return;}
        $this->resticted_where = "$camp='$value'";
    }
    
    public function restoreSession($item){
        if(!is_array($item) || empty($item)) return;
        
        $this->PrepareFk();
        $keys = $this->fkmodel->get1n();
        
        foreach($keys as $name => $k){
            $model = $k['fkey']['model'];
            if($model == LINK) continue; //para o caso de categoria que aponta para subcategorias
            if(isset($item["__".$name])){
                $_SESSION[$model] = $item["__".$name];
                if(isset($item[$name])) $_SESSION["{$model}_title"] = $item[$name];
            }
        }
    }
    
    public function setModelName($model) {
        static $instances = array();
        $this->model_name = $model;
        //echo $this->model_name . "<br/>";
        if(!isset($instances[$model])){
            $exp    = explode("/", $model);
            $class  = end($exp);
            $module = array_shift($exp);
            $this->LoadData($model, 'dm', false);
            $instances[$model] = $this->dm;
            if($this->dm == null) {
                //echo "($model/{$module}_{$class}Data)<br/>";
                return;
            }
        }else $this->dm = $instances[$model];
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
        if($orderby == "" && (array_key_exists("ordem", $this->dados)))
            $orderby = "$this->tabela.ordem ASC";
        /*
        foreach($this->dados as $name => $arr){
            if(array_key_exists('especial', $arr) && $arr['especial'] == 'session' &&
               array_key_exists('session' , $arr) && isset($_SESSION['session'])){
                die("fooon");
            }
        }*/
        if(isset($this->resticted_where) && $this->resticted_where !== ""){
            $where = ($where === "")?$this->resticted_where:"$this->resticted_where AND ($where)";
        }
        $var= $this->db->Read($this->tabela, $campos, $where, $limit, $offset, $orderby);
        if(!$this->cache) $sentencas[$cachevar] = $var;
        //echo $this->db->getSentenca();
        return $var;
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
            foreach($v as $a) $out[] = $a[$field];
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
        if(!array_key_exists($campo, $this->dados)) return false;
        if(!array_key_exists('fkey', $this->dados[$campo])) return false;
        extract($this->dados[$campo]['fkey']);
        if($cardinalidade != 'n1') return false;
        $this->LoadModel($model, 'md');
        
        $qtd = isset($limit)? $limit:"10";
        
        $where = "";
        if($cod_pkey != ""){
            $dados = $this->md->getDados();
            foreach($dados as $nm => $d){
                if(!isset($d['fkey'])) continue;
                if($d['fkey']['model'] == $this->model_name){
                    $where = ("$nm = '$cod_pkey'");
                    break;
                }
            }
        }
        if(isset($filther) && $filther != ""){
            $where = ($where != "")?"$where AND ($filther)":"$filther";
        }
        return $this->md->paginate($page, $link, "", "", $qtd, array(), $where);
    }
    
    public function paginate($page, $link = "", $cod_item = "", $campo = "", $qtd =20, $campos = array(), $adwhere = "", $order = ""){
        $this->LoadResource("html/paginator", 'pag');
        //$this->pag->startDebug();
        $this->LoadResource("html", 'html');

        $where = ($cod_item == "")?$cod_item : $this->genWhere($campo, $this->pkey, $cod_item);
        if($where != ""){
            if(trim($adwhere) != "") $where .= " AND( $adwhere ) ";
        }
        else $where = $adwhere;
        
        $link = ($link == "")? CURRENT_MODULE . "/" . CURRENT_CONTROLLER."/show/" : $link;
        //$link = $this->html->getLink($link, true, true);
        $campos = $this->getCampos($campos);
        $this->pag->setJoin($this->db->getJoin());
        $this->pag->setWhere($where);
        $this->pag->Paginacao($this->tabela, $link, $qtd, $page);
        
        $pk = is_array($this->pkey)?implode(", ", $this->pkey):$this->pkey;
        if($order == "") $ordem = array_key_exists("ordem", $this->dados)?"ordem DESC":"$this->tabela.$pk DESC";
        else $ordem = $order;
        $var =  $this->pag->selecionar($this, $campos, $where, $ordem);
        //print_r($var);die();
        return $var;
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
            $v  = cookie::getVar($nm);
            if($v == ""){
                foreach($this->dados as $name => $value){
                    if(array_key_exists('title', $value)){
                        $this->item_title = $name;
                        cookie::setVar($nm, $name);
                        break;
                    }
                }
            }else $this->item_title = $v;
        }
        return (isset($item[$this->item_title]))?$item[$this->item_title]:'';
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
        static $ret = array();
        if(!$refresh && $campos == array()){
            if(array_key_exists($this->model_name, $ret)){
                if(array_key_exists($cod_item, $ret[$this->model_name])){
                    $this->db->clearJoin();
                    return $ret[$this->model_name][$cod_item];
                }
            }
        }
        $this->PrepareFk();
        $campo = ($campo == "") ?$this->pkey :$campo;
        $campo    = $this->antinjection($campo);
        $cod_item = $this->antinjection($cod_item);
        if(is_array($campo)){
            for($i = 0 ; $i < count($campo); $i++){
                $c = $campo[$i];
                $j = isset($cod_item[$i])?$cod_item[$i]:$cod_item;
                $where[] = "`$c` = '$j'";
            }
            $where = implode(" AND ", $where);
        }
    	else $where = "`$this->tabela`.`$campo` = '$cod_item'";
        $vars = $this->selecionar($campos,$where, "1");
        //print_r($var);echo $this->db->getSentenca() . "<hr/>";
        if(empty ($vars)) return $vars;
        
        $out = array();
        if(empty($campos)){$campos = array_keys($this->dados);}
    	foreach($vars as $i => $var){
            foreach($campos as $name){
                if(!isset($this->dados[$name])){continue;}
                $value = $this->dados[$name];
                if(array_key_exists('title', $value)){
                    $this->item_title = $name;
                }
                
                if(array_key_exists("fkey", $value)){
                    $model = $value['fkey']['model'];
                    $this->LoadModel($model, "temp_model");
                    $card = "fk".$value['fkey']['cardinalidade'];
                    if($card == '1n' && !isset($var[$name])) $var[$name] = $cod_item;
                    if(isset($var[$name])){$var["__$name"] = $var[$name];}
                    $var[$name] = $this->$card->selecionar(
                            isset($var[$name])?$var[$name]:"$name", 
                            $value['fkey'], 
                            $campo,
                            $cod_item, 
                            $this->model_name, 
                            isset($value['fkey']['sort'])?$value['fkey']['sort']:""
                    );
                    //echo "<br/>$name <br/> ";print_r($var[$name]); echo "<br/>".$this->db->getSentenca() . "<br/> \n ".$this->$card->getErrorMessage() ."<br/><hr/>\n\n";
                    if($value['fkey']['cardinalidade'] == "nn" && is_array($var[$name])) $var["__$name"] = array_keys ($var[$name]);
                }elseif(array_key_exists("type", $value)){
                    if($value['type'] == "enum" && isset($var[$name])) {
                        $var["__$name"] = $var[$name]; 
                        $var[$name] = (@$value['options'][$var[$name]]);
                    }
                }
            }
            
            if(!is_array($campo)&& $campos == array()){
                if(array_key_exists($campo, $var)){
                    if(!is_array($var[$campo]))
                        $ret[$this->model_name][$var[$campo]] = $var;
                }
            }
            
            foreach($this->dados as $name => $arr){
                if(array_key_exists($name, $var)){
                    $out[$i][$name] = $var[$name];
                }

                if(array_key_exists("__$name", $var)) {
                    $out[$i]["__$name"] = $var["__$name"];
                }
            }
        }
        
        if(count($out) == 1) return array_shift ($out);
        
        //print_r($out); echo "<br/>\n\n";
        return $out;
    }

    public function getSimpleItem($cod_item, $campos = array(), $coluna = ""){
        $pk = $this->pkey;
        $campos = $this->getCampos($campos); 
        $where  = $this->genWhere($coluna, $pk, $cod_item);
        $var    = $this->selecionar($campos,$where, "1");
        //print_r($var);echo $this->db->getSentenca() . "<hr/>";
        if(empty($var)) return $var;
        
        return array_shift($var);
    }
    
    public function getPlainItem($key){
        $w = "";
        if(is_array($this->pkey)){
            foreach($this->pkey as $pk){
                $k  = array_shift($key);
                $w .= "$pk = '".$this->antinjection($k)."'";
            }
        }else $w = "$this->pkey = '".$this->antinjection($key)."'";
        $v = $this->selecionar(array(), $w, 1);
        if(empty($v)) return $v;
        return array_shift($v);
    }

    public function listSimpleItem($cod_item, $campos = array(), $coluna = ""){
        $pk = $this->pkey;
        $campos = $this->getCampos($campos);
        $where  = $this->genWhere("$this->tabela`.`$coluna", $pk, $cod_item);
        $var = $this->selecionar($campos,$where);
        //echo $this->db->getSentenca();
        return $var;
    }

    public function getCampos($campos = array()){
        
        $ignore_display = false;
        if(!empty($campos)){
            $tmp_camps = array();
            foreach($campos as $camp){
                if(!array_key_exists($camp, $this->dados)) continue;
                $tmp_camps[$camp] = $this->dados[$camp];
            }
            $campos = $tmp_camps;
            $ignore_display = true;
        }else $campos = $this->dados;
        
        $find = array();
        $join = $this->db->getJoin();
        $this->db->setJoin("");
        $used = array($this->tabela => 0);
        foreach($campos as $name => $data){
            if(!$ignore_display && (!array_key_exists("display", $data) || !$data['display'])) continue;
            if(!array_key_exists('fkey', $data)) {$find[] = "$this->tabela.$name"; continue;}
            extract($data['fkey']);
            if($cardinalidade == 'nn' || $cardinalidade == 'n1') continue;
            elseif($cardinalidade == '1n'){
                $this->LoadModel($model, 'fktmp_model');
                $tab     = $this->fktmp_model->getTable();
                $tabname = $tab;
                if(isset($used[$tab])){
                    $used[$tab]++;
                    $tabname = "{$tab}_". $used[$tab];
                    $tab = "$tab as $tabname" ;
                }else $used[$tab] = 0;

                $this->db->join($this->tabela, $tab, array($name), array($keys[0]), "LEFT");
                $find[] = "$tabname.".$keys[1]. " AS $name";
                if(isset($keys[0])) $find[] = "$tabname.".$keys[0]. " AS __$name";
            }
            //cardinalidade 11
            else $find[] = "$this->tabela.$name";
            //$find[] = $name;
        }
        
        $this->db->addJoin($join);
        return $find;
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
        if(!$this->validate()){
            return false;
        }
        
        /*$pkk = $this->pkey;
        if(!is_array($pkk)) $pkk = array($pkk);
        foreach($pkk as $pk){
            if(isset($this->post[$pk]) && @$this->dados[$pk]['ai']) unset($this->post[$pk]);
        }*/
        //insere chaves estrangeiras do tipo 1-1
        $this->PrepareFk();
        if(!$this->fk11->inserir($this->post, $dados)){
            $this->setErrorMessage($this->fk11->getErrorMessage());
            return false;
        }

        //associa os dados
        if(!$this->associa()) return false;

        //insere os dados
        $this->lastid = $this->db->Insert($this->tabela, $this->post);
        if($this->lastid === false){
            $erro = $this->db->getErrorMessage();
            if($erro == "" && !is_array($this->pkey)){
                $e = (DEBUG)?'Debug: '.$this->db->getSentenca():"";
                $erro = "Erro desconhecido ao inserir dados. ". $e;
            }
            
            if($erro != ""){
                $this->setErrorMessage($erro);
                return false;
            }
        }
        if(is_array($this->pkey)){
            $this->lastid = array();
            foreach($this->pkey as $pk){
                $this->lastid[] = $this->post[$pk];
            }
        }
        //echo $this->db->getSentenca() . "<br/><br/>";

        //recupera os dados para insercao de chave estrangeira
        //$lastid = $this->getLastId();
//        insere os dados das chaves estrangeiras n -> 1
//        if(!$this->fkn1->inserir($dados, $lastid, $this->pkey)){
//            $this->setAlertMessage($this->fkn1->getErrorMessage());
//            return false;
//        }
//        
        //insere os dados das chaves estrangeiras n -> n
        if($this->lastid !== false){
            if(!$this->fknn->inserir($dados, $this->lastid, $this->pkey)){
                $this->setAlertMessage($this->fknn->getErrorMessage());
                return true;
            }
        }
        
        //if(!$this->disabled_index) $this->indexar();
        $this->setSuccessMessage("Dados Inseridos Corretamente!");
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
        $this->id = $id;
        if(!$this->validate())return false;
        if(!$this->associa(true)) return false;

        //atualiza o banco de dados
        $where = $this->genWhere($camp, $this->pkey, $id);
        if(!$this->db->Update($this->tabela,$this->post, $where)){
            $this->setErrorMessage($this->db->getErrorMessage());
            //$this->setErrorMessage($this->db->getSentenca());
            return false;
        }
        
        //edita chaves estrangeiras do tipo 1-1
        $this->PrepareFk();
        if(!$this->fk11->editar($post, $id)){
            if($this->fkedit == false){
                $this->setAlertMessage($this->fk11->getErrorMessage());
                return false;
            }
        }

        //seta mensagem de sucesso
        $this->setSuccessMessage("Dados Alterados Corretamente!");
        return true;
    }
    
    /*Atenção ao utilizar esta função, ela não deveria ser pública, está assim só para o
     * caso de listar as chaves estrangeiras N1
     */
    public function genWhere($camp, $pkey, $id){

        $camp = ($camp == "")? $pkey : $camp ;
        $where = "";
        if(is_array($camp) && is_array($id)){
            $prefix = ($camp == $pkey)?"`$this->tabela`.":"";
            $and = "";
            $i = 0;
            foreach($camp as $c){
                $v = $id[$i++];
                if(strstr($c, ".")) $c = str_replace(".", "`.`", $c);
                $where .= "$and $prefix`$c` = '$v'";
                $and = " AND ";
            }
        }
        else{
            if(is_array($camp)) {
                reset($camp);
                $campo   = current($camp);
                $where  = "`$campo` = '$id'";
            }
            elseif(is_array($id)) {
                reset($id);
                $or      = "";
                $campo   = $camp;
                foreach($id as $i){
                    $where  .= "$or `$campo` = '$i'";
                    $or = " OR ";
                }
            }
            else $where = "`$camp` = '$id'";
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
        if($chave == "") $chave = $this->pkey;
        $wh = "";
        
        //seta o where composto
        if(is_array($chave)){
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
            $wh = implode(" AND ", $arr);
        }else {
            //prepara os valores a serem consultados
            $valor = $this->antinjection($valor);
            $chave = $this->antinjection($chave);
            $wh = "`$chave` = '$valor'";
        }
        
        //recupera o id do item para apagar chaves estrangeiras
        $selecao = $this->selecionar(array(), $wh, "1");
        if(!empty($selecao)) $selecao = array_shift($selecao);
        
        //apaga o item
        if(!$this->db->Delete($this->tabela, $wh)){
            $this->setErrorMessage($this->db->getErrorMessage());
            return false;
        }
        //die($this->db->getSentenca());
        //apaga a chave estrangeira
        if(!empty($selecao)){
            if(!$this->fk11->apagar($selecao)){
                $this->setErrorMessage($this->fk11->getErrorMessage());
                return false;
            }
        }
        $this->setSuccessMessage("Conteudo apagado com sucesso!");
        return true;
    }
    
    protected function PrepareFk(){
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
        
        //verifica se tem dados a serem validados
        if(empty ($this->post)){ 
            $this->setErrorMessage("Dados a serem inseridos estão vazios!");
            return false;
        }
        
        if(!is_array($this->pkey) && isset($this->post[$this->pkey]) && 
          ($this->post[$this->pkey] == 0 || $this->post[$this->pkey] == '')){
              unset($this->post[$this->pkey]);
        }
          
        $post = array();
        $this->dados = $this->getDados();
        foreach($this->post as $name => $valor){
            //evita sql injection
            if(array_key_exists($name, $this->dados)){
                if(is_array($valor)){
                    foreach($valor as $i => $v){
                        if(is_object($v) || is_array($v))continue;
                        $pos = strpos($v, "FUNC_");
                        if($pos === false) $post[$name][$i] = $this->antinjection($v);
                        else               $post[$name][$i] = $v;
                    }
                }
                else{
                    $pos = strpos($valor, "FUNC_");
                    if($pos === false) $post[$name] = $this->antinjection($valor);
                    else               $post[$name] = $valor;
                }
            }
            
        }
        if(array_key_exists('antispam', $this->post)) $post['antispam'] = $this->post['antispam'];
        $this->post = $post;
        $this->LoadResource("formulario/validator", "pval");
        if(!$this->pval->validate($this->dados, $this->post)){
            $this->setMessage('validation', $this->pval->getMessages());
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
        
        $data = $this->dados;
        if(!is_array($data) || empty ($data)){
            $this->setErrorMessage("Erro no sistema! Dados a serem inseridos não foram configurados,
                consulte o administrador");
            return false;
        }

        $post = $post2 = array();        
        foreach($this->dados as $tname => $arr){
            
            if(array_key_exists($tname, $this->post)){
                //associa as chaves estrangeiras
                if(!array_key_exists('fkey', $arr) || 
                    $arr['fkey']['cardinalidade'] == '11' ||
                    $arr['fkey']['cardinalidade'] == '1n'){
                    $post[$tname] = $this->post[$tname];
                }
            }
            
        }

        if($this->id == ""){
        //if($edit == false){
            if(is_array($this->pkey)){
                foreach($this->pkey as $pk)
                    if(!array_key_exists($pk, $post))
                        if(array_key_exists('ai', $this->dados[$pk]))
                            $post[$pk] = "FUNC_NULL";
            }
            else{
                if(!array_key_exists($this->pkey, $post))
                    if(array_key_exists($this->pkey, $this->dados) && 
                       array_key_exists('ai', $this->dados[$this->pkey]))
                        $post[$this->pkey] = "FUNC_NULL";
            }
        }
        
        if(empty ($post)){
            $model = @ucfirst(end(explode("/", $this->model_name)));
            $this->setErrorMessage("Caro usuário, os dados do formulário enviado não foram preenchidos no módulo: $model!");
            return false;
        }
        
        $this->post = $post;
       // print_r($this->post); die();
        return true;
    }
    
    
    public function getLastId(){
        if($this->lastid != "") return $this->lastid;
    	$pkey  = $this->pkey;
        $order = "";
    	if(!is_array($pkey)){
            $order = $pkey;
            $index = $pkey;
            $pkey = array($this->pkey);
        }else{
            $index = $pkey[0];
            $virg  = "";
            foreach ($pkey as $pk){
                $order .= "$virg $pk";
                $virg   =  ",";
            }
        }
        
    	$var = $this->selecionar($pkey, "", "1", "", "$order DESC");
        if(empty ($var)) return "";
    	$var = array_shift($var);
        foreach($var as $arr) $out[] = $arr;
        if(count($out) == 1)  $out = array_shift($out);
    	return ($out);
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
        
        if($permSimpleTags){
            if(is_array($sql)){
                foreach($sql as &$sq){
                    if(is_array($sq)) continue;
                    $sq = $this->tratamento($sq);
                }
            }else $sql = $this->tratamento($sql);
        }
        else {
            $sql = strip_tags($sql, "<p><br/><span><img><a>");
            $sql = str_replace(array("'"), array('"'), $sql);
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
    
    public function join($modeldst, $ksrc = "", $kdst = "", $join = 'LEFT', $modelsrc = ''){
        $ksrc = ($ksrc == "")?$this->pkey:$ksrc;
        $tb = $this->LoadModel($modeldst, 'tmp_model')->getTable();
        if($kdst == ""){
            $dados = $this->tmp_model->getDados();
            foreach($dados as $key => $var){
                if(!isset($var['fkey'])) continue;
                if($var['fkey']['model'] != $this->model_name || !in_array($var['fkey']['model'], array('11', '1n'))) continue;
                $kdst = $key; 
                break;
            }
        }
        if(!is_array($ksrc)){$ksrc = array($ksrc);}
        if(!is_array($kdst)){$kdst = array($kdst);}
        $tabela = ($modelsrc === "")?$this->tabela:$this->LoadModel($modelsrc, 'tmp_model')->getTable();
        $this->db->Join($tabela, $tb, $ksrc, $kdst, $join);
        return $tb;
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
}