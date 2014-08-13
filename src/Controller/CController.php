<?php

namespace classes\Controller;
use classes\Classes\EventTube;
use classes\Classes\cookie;
class CController extends \classes\Controller\Controller {

    /**
     *Variável utilizada para proteger o conteúdo para ser exibido apenas para o seu autor
     * Ela deve conter o nome do campo que guarda o código do usuário, se for vazia não fará a proteção
     * @var varchar nome do campo do usuário 
     */
    protected $autor_camp = "";
    public $model_name     = "";
    protected $sess_cont_alerts = 'controller_alerts';
    public function  __construct($vars) {
        parent::__construct($vars);
        if($this->model_name != "") {
            $this->LoadModel($this->model_name, "model");
            if($this->model->blockBecauseFeature()){
                throw new \classes\Exceptions\AcessDeniedException();
            }
        }
        if(!isset($_REQUEST['ajax'])){
            $this->registerVar("model", $this->model_name);
            $this->registerVar("component", $this->model_name);
        }
    }
    
    protected $cod  = "";
    protected $item = array();
    protected $free_cod = array('index', 'formulario', 'grid');
    protected $blocked_actions = array();
    protected $redirect_link   = array();
    
    protected function addToFreeCod($action){
        if(!is_array($action)) $this->free_cod[] = $action;
        else $this->free_cod = array_merge ($this->free_cod, $action);
    }

    private function checkUrl(){
        if(in_array(CURRENT_ACTION, $this->blocked_actions)){
            if(!in_array("index", $this->blocked_actions)) Redirect($this->model_name);
            else Redirect ('');
        }
    }
    public function AfterLoad(){
        //inicializa as variáveis
        $this->checkUrl();
        //gerencia as sessions
        $this->cod  = isset($this->vars[0])?$this->vars[0]:"";
        if(in_array(CURRENT_ACTION, $this->free_cod)&& isset($_SESSION[$this->model_name])) unset($_SESSION[$this->model_name]);
        elseif($this->cod != "")$_SESSION[$this->model_name] = $this->cod;
        elseif(isset($_SESSION[$this->model_name])) Redirect(CURRENT_URL ."/".$_SESSION[$this->model_name]);
        
        
        if($this->cod != "" && !in_array(CURRENT_ACTION, $this->free_cod)){
            $this->registerVar("cod", $this->cod);
            if(method_exists($this->model, 'getItem')){
                $this->item = $this->model->getItem($this->cod, "", true);
                if(empty($this->item)){
                    $vars['erro'] = "Este item já foi apagado ou nunca existiu!";
                    $vars['status'] = "0";
                    Redirect ($this->model_name, 0 , "", $vars);
                }
                
                //restaura as sessions
                $this->model->restoreSession($this->item);
                
                //gera o título do item
                $title = $this->model->getItemTitle($this->item);
                if($title != "") $this->genTags($title);
            }
        }
        
        $this->onlyAutor();
        //gera as tags
        if(!empty ($this->item)){
            $dados = $this->model->getDados();
            $resumo = $titulo = "";
            foreach($this->item as $name => $arr){
                if(!array_key_exists($name, $dados)) continue;

                $arr = $dados[$name];
                if(array_key_exists('title', $arr)         && $arr['title']) $_SESSION["{$this->model_name}_title"] = $this->item[$name];
                if(!array_key_exists('seo', $arr)) continue;
                if(array_key_exists("titulo", $arr['seo']) && $titulo == "") $titulo = $this->item[$name];
                if(array_key_exists("resumo", $arr['seo']) && $resumo == "") $resumo = $this->item[$name];
            }
            $this->genTags($titulo , $resumo, str_replace(" ", " ,", $titulo));
            $this->genImageTag($this->item);
        }
        
        if(!isset($_REQUEST['ajax'])){$this->registerVar("item", $this->item);}
        elseif(!empty ($this->item)){$this->registerVar('item', $this->item);}
        if(cookie::cookieExists($this->sess_cont_alerts)){
            //print_r(cookie::getVar($this->sess_cont_alerts));die();
            $this->setVars(cookie::getVar($this->sess_cont_alerts));
            cookie::destroy($this->sess_cont_alerts);
        }
        
        $item = empty($this->item)?array():$this->item;
        $this->LoadClassFromPlugin($this->model_name . "/" . CURRENT_CONTROLLER . "Tag", 'tag', false);
        if($this->tag != null) {$this->setTags($this->tag->getTagsOfPage(CURRENT_ACTION, $item));}
        
        $this->LoadApps();
    }
    
    private $listType = "listar";
    public function setListType($ltype){
        $this->listType = $ltype;
    }

    public function getIndexListType(){
        return $this->listType;
    }
    
    private $paginate_method = 'paginate';
    public function setPaginateMethod($method){
        if(method_exists($this->model, $method))
            $this->paginate_method = $method;
    }
    
    public function index($display = true, $link = ""){
        $link = ($link == "")? "admin/auto/areacliente/page":$link;
        $this->setPage();
        if($this->getTag('page_title') == ""){
            $this->genTags(CURRENT_MODULE ." ", CURRENT_MODULE);
        }
        
        $method = $this->paginate_method ;
        $item = $this->model->$method($this->page, CURRENT_PAGE);
        if(empty($item) && !$this->prevent_red){
            $this->LoadClassFromPlugin('usuario/perfil/perfilPermissions', 'pperm');
            $this->pperm->RedirectIfHasPermission($this->model_name. "/formulario");
        }
        $this->registerVar("item"        , $item);
        $this->registerVar("comp_action" , $this->getIndexListType());
        $this->registerVar("show_links"  , '');
    	if($display) $this->display($link);
    }
    
    public function show($display = true, $link = ""){
        $link = ($link == "")? "admin/auto/areacliente/page":$link;
        $this->registerVar("comp_action" , 'show');
    	if($display) $this->display($link);
    }
    
    public function formulario($display = true, $link = ""){
        $desc = $this->model->getModelDescription();
        if($desc != "") EventTube::addEvent('body-top', "<div class='container-msg info'>$desc</div>");
        $this->forms($display, $link);
    }
    
    public function edit($display = true, $link = ""){
        $this->forms($display, $link);
    }

    protected $redirect_droplink = LINK;
    public function apagar(){
        if($this->cod == ""){
            if($this->prevent_red){
                $this->registerVar('status', '0');
                $this->registerVar('erro', 'O código deste item não pode estar vazio');
                return;
            }
            Redirect($this->redirect_droplink);
        }
        
        if($this->model->apagar($this->cod)){
            $vars = $this->model->getMessages();
            if(empty($vars)) $vars['success'] = "Conteudo removido com sucesso!";
            
            if(!isset($_REQUEST['ajax'])){cookie::setVar($this->sess_cont_alerts, $vars);}
            $vars['status'] = "1";
            $this->registerVar('status', '1');
            if(isset($_SESSION[LINK])) unset($_SESSION[LINK]);
            Redirect($this->redirect_droplink, 0, "", $vars);
            //die(json_encode($vars));
        }
        $this->setVars($this->model->getMessages());
        $this->registerVar('status', "0");
        $this->genTags("Apagar Dados");
        $this->show();
    }
    
    protected $sublistview = "admin/auto/areacliente/page";
    public function sublist(){
        
        if(!isset($this->vars[1])) Redirect (LINK."/show");
        $page  = isset($this->vars[2])?$this->vars[2]:0;
        $campo = $this->vars[1];
        
        $link = $this->model_name ."/sublist/$this->cod/$campo";
        $this->item[$campo] = $this->model->getSublist($page, $campo, $link, $this->cod);
        
        /*$dados = $this->model->getDados();
        foreach($dados as $nm => $dd){
            if($nm == $campo) continue;
            if(!isset($dd['fkey'])) continue;
            if(!$dd['fkey']['cardinalidade'] != "n1") continue;
            //if(isset($this->item[$name])) unset($this->item[$name]);
        }*/
        
        if($this->item[$campo] === false) {die('faaalse');Redirect (LINK."/show");}
        $this->registerVar("item", $this->item);
        $this->registerVar("comp_action" , 'show');
    	$this->display($this->sublistview);
        //$this->display("admin/auto/areacliente/page");
    }
    
    public function group(){
        $arr = ($this->model->getCategorizedRow());
        if($arr != ""){
            $model = $arr['fkey']['model'];
            $cod_cat = isset($_SESSION[$model])?$_SESSION[$model]:"";
            if($cod_cat != ""){
                Redirect ("$model/$cod_cat");
            }
            
            if(!empty($this->item)){
                Redirect ("$model/".$this->item[$arr['name']]);
            }
        }
        
        Redirect(CURRENT_MODULE);
    }
    
    public function hasOwn(){
        $row = $this->model->getAltenticationRow();
        if($row == "") {return true;}
        if(empty($this->item)){return true;}
        $this->LoadModel('usuario/login', 'uobj');
        $cod = $this->uobj->getCodUsuario();
        if(!$this->item[$row] == $cod){return false;}
        return true;
    }
    
    private $prevent_red = false;
    protected function prevent_redirect(){
        $this->prevent_red = true;
    }
    protected function forms($display = true, $link = ""){
        $link = ($link == "")? "admin/auto/formulario":$link;
        if($this->getTag('page_title') == ""){
            $nome = (CURRENT_CONTROLLER == 'index')?CURRENT_MODULE:CURRENT_CONTROLLER;
            $nome = (($this->cod == "")? "Criar ":"Editar ") . $nome;
            $this->genTags(ucfirst($nome));
        }
        
        $this->registerVar('titulo', $this->getTag('page_title'));
        if(!empty($_POST)){
            //print_r($_POST);
            $lastid = "";
            if($this->cod != ""){ $status = $this->model->editar($this->cod, $_POST); }
            else                { $status = $this->model->inserir($_POST); $lastid = $this->model->getLastId();}
            
            $id             = ($this->cod != "")?$this->cod:$lastid;
            if($id === false || $id === 0) $id = "";
            $messages       = $this->model->getMessages();
            $vars           = $messages;
            $vars['status'] = ($status == true)? 1:0;
            $vars['id']     = $id;
            if(isset($_GET['item']) && $_GET['item'] == 'plain'){ 
                $vars['item']          = $this->model->getPlainItem($id); 
                $vars['item_protocol'] = 'plain';
            }
            else  {
                $vars['item']          = $this->model->getItem($id);
                $vars['item_protocol'] = 'auto';
            }
            $this->setVars($vars);
            
            if($status == true && !$this->prevent_red){
                if(!isset($_REQUEST['ajax'])){cookie::setVar($this->sess_cont_alerts, $messages);}
                $id = is_array($id)?implode("/", $id):$id;
                $page = (array_key_exists(CURRENT_ACTION, $this->redirect_link))?$this->redirect_link[CURRENT_ACTION]:LINK."/show/$id";
                Redirect($page, 0, "", $vars);
            }
        }
        
        if(!array_key_exists('ajax', $_REQUEST) || !$_REQUEST['ajax']){
            $dados = (!isset($this->dados))?$this->model->getDados():$this->dados;
            $this->registerVar('dados', $dados);
            $formulario = ($this->cod == "")?"":$this->model->getItem($this->cod);
            $this->registerVar('values', $formulario);
        }
        if($display) $this->display($link);
    }
    
    protected function redirect($page){
        $dados = $this->model->getMessages();
        foreach($this->variaveis as $name => $var) $dados[$name] = $var;
        if(!isset($_REQUEST['ajax'])){cookie::setVar($this->sess_cont_alerts, $dados);}
        Redirect($page, 0, "", $dados);
    }
    
    private function LoadApps(){
        if($this->cod == "") return;
        
        $this->LoadModel('app/aplicativo', 'app');
        $this->LoadResource('html', 'html');
        $apps = $this->app->getApps(LINK);
        if(empty($apps)) return;
        
        $var  = '';
        foreach($apps as $app){
            $link = $this->html->getLink(LINK."/app/$this->cod/{$app['cod']}/index");
            $var .= "<a href='$link'>{$app['titulo']}</a>";
        }
        EventTube::addEvent('body-top', $var);
    }
    
    public function app(){
        if(!isset($this->vars[0])) Redirect (LINK . "/show");
        $cod_app = array_shift($this->vars);
        $method  = array_shift($this->vars);
        $this->LoadModel('app/aplicativo', 'app');
        $app = $this->app->getItem($cod_app);
        if(empty($app)) Redirect (LINK . "/show");
        $model = "app/{$app['aplicativo']}";
        $this->LoadClassFromPlugin("$model/{$app['aplicativo']}Controller", 'cont');
        if(!method_exists($this->cont, $method)) $method = 'index';
        $this->cont->setDocument(LINK."/$this->cod");
        $this->cont->$method();
    }
    
    protected function onlyAutor(){
        //if($this->autor_camp == "" || \usuario_loginModel::IsWebmaster()){return;}
        if($this->autor_camp == ""){return;}
        $autor = \usuario_loginModel::CodUsuario();
        if(empty($this->item)){
            $this->model->restrictByAutor($this->autor_camp, $autor);
            return;
        }
        if(!isset($this->item[$this->autor_camp])){
            throw new \classes\Exceptions\PageBlockedException("Você não tem permissão para acessar esta página");
        }
        $campval = isset($this->item['__'.$this->autor_camp])?$this->item['__'.$this->autor_camp]:$this->item[$this->autor_camp];
        if($autor == 0){$this->LoadModel('usuario/login', 'uobj')->needLogin();}
        if($autor !== $campval){
            throw new \classes\Exceptions\PageBlockedException("O conteúdo contido nesta página só pode ser exibido para os autores do conteúdo");
        }
    }
    
    public function grid(){
        $this->display('admin/auto/areacliente/grid');
    }
}