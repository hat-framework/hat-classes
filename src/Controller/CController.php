<?php

namespace classes\Controller;
use classes\Classes\EventTube;
use classes\Classes\session;
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
        $this->LoadModel('plugins/action', 'act');
    }
    
    protected $cod             = "";
    protected $current_action  = "";
    protected $item            = array();
    protected $free_cod        = array('index', 'formulario', 'grid', 'search');
    protected $blocked_actions = array();
    protected $redirect_link   = array();

    protected function getCanonicalCurrentAction(&$action){        
        while(is_numeric($action[strlen($action)-1])){
            $action = substr($action, 0, strlen($action)-1);
        }
    }
    
    public function AfterLoad(){
        $this->current_action = CURRENT_ACTION;
        $this->getCanonicalCurrentAction($this->current_action);
        $this->checkUrl();
        $this->detectParams();
        $this->processSessions();
        parent::AfterLoad();
        $this->LoadApps();
    }
    
            private function checkUrl(){
                if(!in_array($this->current_action, $this->blocked_actions)){return;}
                $url =(!in_array("index", $this->blocked_actions))?$this->model_name:"";
                Redirect($url);
            }

            protected function detectParams(){
                $autor = \usuario_loginModel::CodUsuario();
                $url   = substr(CURRENT_PAGE, 0, strlen(CURRENT_PAGE)-1);
                if(!in_array($this->current_action, $this->free_cod) && $this->act->needCode($url)){
                    $this->cod = isset($this->vars[0])?$this->vars[0]:"";
                    $this->manageSessions();
                    $this->prepareItem();
                    $this->onlyAutor();
                    $this->generateItemTags();
                    return $this->registerItem();
                }
                $this->addToFreeCod($this->current_action);
                if(isset($_SESSION[$this->model_name])) {unset($_SESSION[$this->model_name]);}
                $this->model->setRestriction($this->autor_camp, $autor);
            }

                    protected function manageSessions(){
                        if($this->cod != ""){$_SESSION[$this->model_name] = $this->cod;}
                        elseif(isset($_SESSION[$this->model_name])){Redirect(CURRENT_URL ."/".$_SESSION[$this->model_name]);}
                    }

                    protected function prepareItem(){
                        $this->registerVar("cod", $this->cod);
                        if(!method_exists($this->model, 'getItem')){return;}
                        $this->item = $this->model->getItem($this->cod, "", true);
                        if(empty($this->item)){
                            $vars['erro'] = "Este item já foi apagado ou nunca existiu!";
                            $vars['status'] = "0";
                            Redirect ($this->model_name, 0 , "", $vars);
                        }

                        //restaura as sessions
                        $this->model->restoreSession($this->item);
                    }

                    protected function onlyAutor(){
                        if($this->autor_camp == ""){return;}
                        $autor = \usuario_loginModel::CodUsuario();
                        if(!isset($this->item[$this->autor_camp])){
                            throw new \classes\Exceptions\PageBlockedException("Você não tem permissão para acessar esta página");
                        }
                        $campval = isset($this->item['__'.$this->autor_camp])?$this->item['__'.$this->autor_camp]:$this->item[$this->autor_camp];
                        if($autor == 0){$this->LoadModel('usuario/login', 'uobj')->needLogin();}
                        if($autor !== $campval){
                            throw new \classes\Exceptions\PageBlockedException("O conteúdo contido nesta página só pode ser exibido para os autores do conteúdo");
                        }
                    }

                    protected function generateItemTags(){
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
                        if($titulo === ""){
                            $titulo = $this->model->getItemTitle($this->item);
                        }
                        $this->genTags($titulo , $resumo, str_replace(" ", " ,", $titulo));
                        $this->genImageTag($this->item);
                    }

                    protected function registerItem(){
                        if(empty ($this->item)){return;}
                        $this->registerVar("item", $this->item);
                    }

                    protected function addToFreeCod($action){
                        if(!is_array($action)) $this->free_cod[] = $action;
                        else $this->free_cod = array_merge ($this->free_cod, $action);
                    }

            private function processSessions(){
                if(!session::exists($this->sess_cont_alerts)){return;}
                $this->setVars(session::getVar($this->sess_cont_alerts));
                session::destroy($this->sess_cont_alerts);
            }

            private function LoadApps(){
                if($this->cod == "") {return;}

                $this->LoadModel('app/aplicativo', 'app');
                $this->LoadResource('html', 'html');
                $apps = $this->app->getApps(LINK);
                if(empty($apps)) {return;}

                $var  = '';
                foreach($apps as $app){
                    $link = $this->html->getLink(LINK."/app/$this->cod/{$app['cod']}/index");
                    $var .= "<a href='$link'>{$app['titulo']}</a>";
                }
                EventTube::addEvent('body-top', $var);
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
        if(isset($this->redirect_link['apagar']) && $this->redirect_link['apagar'] != ""){
            $this->redirect_droplink = $this->redirect_link['apagar'];
        }
        $this->checkCode();
        $this->doDrop();
        $this->setVars($this->model->getMessages());
        $this->registerVar('status', "0");
        $this->genTags("Apagar Dados");
        $this->show();
    }
    
            private function checkCode(){
                if($this->cod != ""){return;}
                if($this->prevent_red){
                    $this->registerVar('status', '0');
                    $this->registerVar('erro', 'O código deste item não pode estar vazio');
                    return;
                }
                Redirect($this->redirect_droplink);
            }
    
            private function doDrop(){
                if(false === $this->model->apagar($this->cod)){return;}
                $vars = $this->model->getMessages();
                if(empty($vars)){$vars['success'] = "Conteudo removido com sucesso!";}

                if(!isset($_REQUEST['ajax'])){session::setVar($this->sess_cont_alerts, $vars);}
                $vars['status'] = "1";
                $this->registerVar('status', '1');
                if(isset($_SESSION[LINK])) unset($_SESSION[LINK]);
                Redirect($this->redirect_droplink, 0, "", $vars);
            }
    
    protected $sublistview = "admin/auto/areacliente/page";
    public function sublist(){
        if(!isset($this->vars[1])) Redirect (LINK."/show");
        $page  = isset($this->vars[2])?$this->vars[2]:0;
        $campo = $this->vars[1];
        
        $link = $this->model_name ."/sublist/$this->cod/$campo";
        $this->item[$campo] = $this->model->getSublist($page, $campo, $link, $this->cod);
        
        if($this->item[$campo] === false) {Redirect (LINK."/show/$this->cod");}
        $this->registerVar("item", $this->item);
        $this->registerVar("comp_action" , 'show');
    	$this->display($this->sublistview);
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
        if($link == ""){$link = "admin/auto/formulario";}
        $this->setPageTitle();
        $this->processPost($_POST);
        $this->verifyAjax();
        if($display) {$this->display($link);}
    }
            private function setPageTitle(){
                if($this->getTag('page_title') == ""){
                    $nome = (CURRENT_CONTROLLER == 'index')?CURRENT_MODULE:CURRENT_CONTROLLER;
                    $nome = (($this->cod == "")? "Criar ":"Editar ") . $nome;
                    $this->genTags(ucfirst($nome));
                }
                $this->registerVar('titulo', $this->getTag('page_title'));
            }
    
            private function processPost($post){
                if(empty($post)){return;}
                //print_r($post);
                $lastid = "";
                if($this->cod != ""){ $status = $this->model->editar($this->cod, $post); }
                else{$status = $this->model->inserir($post); $lastid = $this->model->getLastId();}

                $id       = ($this->cod != "")?$this->cod:$lastid;
                $messages = $this->model->getMessages();
                $vars     = $messages;
                if($id === false || $id === 0) {$id = "";}
                $this->findItem($vars, $status, $id);
                $this->setVars($vars);
                if($status != true || $this->prevent_red){return;}
                if(!isset($_REQUEST['ajax'])){session::setVar($this->sess_cont_alerts, $messages);}
                if(is_array($id)){$id = implode("/", $id);}
                $page = (array_key_exists($this->current_action, $this->redirect_link))?$this->redirect_link[$this->current_action]:LINK."/show/$id";
                Redirect($page, 0, "", $vars);
            }
            
                    private function findItem(&$vars, $status, $id){
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
                    }
            
            private function verifyAjax(){
                if(!array_key_exists('ajax', $_REQUEST) || !$_REQUEST['ajax']){
                    $dados = (!isset($this->dados))?$this->model->getDados():$this->dados;
                    $this->registerVar('dados', $dados);
                    $formulario = ($this->cod == "")?"":$this->model->getItem($this->cod);
                    if($formulario !== ""){$this->registerVar('values', $formulario);}
                }
            }
    
    protected function redirect($page){
        $dados = $this->model->getMessages();
        foreach($this->variaveis as $name => $var) $dados[$name] = $var;
        if(!isset($_REQUEST['ajax'])){session::setVar($this->sess_cont_alerts, $dados);}
        Redirect($page, 0, "", $dados);
    }
    
    public function search($display = true, $link = ""){
		if($link == ""){$link = "admin/auto/areacliente/page";}
        $where = $this->prepareWhere();
		$qtd   = isset($_GET['_qtd'])?$_GET['_qtd']:20;
        $this->setPage();
        $method = $this->paginate_method;
        $item   = $this->model->$method($this->page, CURRENT_PAGE, '','',$qtd,array(),$where);
        $this->registerVar("item"        , $item);
        $this->registerVar("comp_action" , 'listInTable');
        $this->registerVar("show_links"  , '');
    	if($display) $this->display($link);
    }
	
			private function prepareWhere(){
				$str  = array();
				$dados = $this->model->getDados();
				foreach($_GET as $name => $var){
					if(!array_key_exists($name, $dados)){continue;}
					$k = "_{$name}_op";
					if(array_key_exists($k, $_GET)){
						if($_GET[$k] == "LIKEP"){$str[] = "$name LIKE '$var%'";}
						elseif($_GET[$k] == "PLIKEP"){$str[] = "$name LIKE '%$var%'";}
						else{$str[] = "$name {$_GET[$k]} '$var'";}
					}else{$str[] = "$name='$var'";}
				}
				$where = implode(' AND ', $str);
				return $where;
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
    
	  public function grid2() {
      $var = $this->getVar('css');
      if($var === ""){
        $this->registerVar('css', array());
      }
      $query = "";
      if(!empty($_GET) && count($_GET) > 1){
        $temp = $this->LoadResource('formulario/filter', 'sgen')->getQuery($_GET, $this->model_name);
        $query = implode(" AND ", $temp);
      }
      $this->model->importDataFromCsv(null);
      $this->setVars($this->model->getMessages());
      $this->setPage();
      $item = $this->model->paginate($this->page, CURRENT_PAGE, "", "",10,array(), $query);
      $this->registerVar("item", $item);
      $this->display('admin/auto/areacliente/grid2');
    }
	
    public function grid(){
        $this->display('admin/auto/areacliente/grid');
    }
    
    public function gadget(){
        if(!isset($this->vars[0])){Redirect(CURRENT_CONTROLLER);}
        $page        = (isset($this->vars[1]))?$this->vars[1]:1;
        $cod_usuario = \usuario_loginModel::CodUsuario();
        $limit       = 10;
        $this->registerVar(
                'gadgetData',
                $this->LoadModel('site/gadget', 'sga')
                    ->setPage($page)
                    ->setCodUser($cod_usuario)
                    ->setLimit($limit)
                    ->getGadgetData($this->vars[0])
        );
        $this->registerVar('gadget', $this->sga->getItem($this->vars[0]));
        $this->display('site/gadget/index');
    }
	
	public function changeMenu(){
		
	}
}
