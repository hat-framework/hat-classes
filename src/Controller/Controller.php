<?php

namespace classes\Controller;
use classes\Classes\Object;
use classes\Classes\cookie;
use classes\Classes\View;
abstract class Controller extends Object {

        protected $vars 	 = array();
        protected $variaveis     = array();
        private   $tags 	 = array();   
        private   $template	 = "";
        protected $app_name	 = "";
        protected $view 	 = NULL;
        private   $ajax 	 = false;

        public function  __construct($vars) {
            $this->vars = $vars;
        }

        public function enableAjax(){
            $this->ajax = true;
        }
        
        public function setCurrentApp($app){
            $this->app_name = $app;
        }
        
        public function disableAjax(){
            $this->ajax = false;
        }
        
        abstract function index();
        
        //chamado antes de aplicar as regras de segurança e gerar o menu
        public function AfterLoad(){}
        
        //chamado depois de carregar o menu e a segurança mas antes de executar a ação
        public function BeforeLoad(){}
        
        //chamado depois de carregar o menu e a segurança mas antes de executar a ação
        public final function setBreadcrumb(){
            $this->LoadModel('site/menu', 'mn')->setBreadscrumb();
        }
        
        //chamdo após executar a ação
        public function BeforeExecute(){}
        
        //chamado antes de executar a ação para verificar se usuário pode acessar a página.
        public function hasPermission(){return true;}
        
        public final function setTemplate($template){
            $this->template = $template;
        }
        
        public final function display($action, $vars = array()){
        
            //seta as variaveis
            $this->setVars($vars);
            //carrega a view
            $this->view = new View();
            $this->view->registerVars($this->variaveis);
            $this->view->setTags($this->tags);
            $this->view->setTemplate($this->template);
            if($this->ajax) $this->view->enableAjax();
            $this->view->execute($action);
        }
        
        public final function setVars($vars){
            if(is_array($vars) && !empty($vars)){
            	$this->variaveis = array_merge($vars, $this->variaveis);
            }
        }
        
        public final function getVars(){
            return $this->variaveis;
        }


        protected function unregisterVar($name){
            if(array_key_exists($name, $this->variaveis)) unset($this->variaveis[$name]);
        }
        
        public final function registerVar($name, $var){
            $this->variaveis[$name] = $var;
        }
        
        public final function clearVars(){
            $this->variaveis = array();
        }
        
        public final function jsonVars(){
            die(json_encode($this->variaveis));
        }
        
        public final function getVar($name){
            return (array_key_exists($name, $this->variaveis))? $this->variaveis[$name]:"";
        }
        
        protected final function genTags($titulo, $descricao = "", $keywords = "", $robots = "FOLLOW"){

            $this->tags['robots']      = $robots;
            if($titulo == "" && $keywords == "" && $descricao == "") return;
            if($keywords != "" && $titulo == "") $titulo = $keywords;
            
            if(is_array($titulo)) $titulo = array_shift ($titulo);
            if($titulo    != "") $this->tags['page_title']  = strip_tags ($titulo);
            if($keywords  != "") $this->tags['keywords']    = strip_tags ($keywords);
            if($descricao != "") $this->tags['description'] = strip_tags ($descricao);
        }
        
        protected final function genImageTag($item){
            if(array_key_exists("album", $item)){
                $this->LoadComponent('galeria/album/album', 'gfoto');
                $album = $item['album']['cod_album'];
                //$link  = $this->gfoto->getLinkCapa($album, "medium", false);
                $this->setTag('image', $this->gfoto->getLinkCapa($album, "medium", false));
            }
        }
        
        protected final function setTags($tags){
            $this->tags = $tags;
        }

        protected final function setTag($tagname, $value){
            $this->tags[$tagname] = $value;
        }
        
        protected final function getTag($tagname){
            return isset($this->tags[$tagname])?$this->tags[$tagname]:"";
        }
        
        protected function setPage(){
            $page = array_shift($this->vars);
            $this->page = ($page == "")? "0": is_numeric($page)? $page: "0";
            $this->page = ($this->page < 0)? 0:$this->page;
        }
        
        protected final function MDRedirect($url = ""){
            $url  = ($url == "")?LINK."/show/$this->cod":$url;
            $vars = $this->getVars();
            cookie::setVar($this->sess_cont_alerts, $vars);
            Redirect($url, 0, "", $vars);
        }
}