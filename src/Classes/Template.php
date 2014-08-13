<?php

namespace classes\Classes;
class Template extends Object{
    
    private $vars = array();
    private $tags = array();
    private $ajax = false;
    
    public function __construct($template) {
        require_once CONFIG . "template.php";
        $this->LoadResource("html/html", "Html");
        $this->gui = new \classes\Component\GUI();
        //seta o novo template
        $template       = isset($_GET['template'])?$_GET['template']:$template;
        $this->template = ($template == "")?CURRENT_TEMPLATE:$template;

        //verifica se o template setado existe, aplica o default caso nao exista
        $file = TEMPLATES."$this->template/$this->template"."Template.php" ;
        if(!file_exists($file)) $this->template = CURRENT_TEMPLATE;
    }
    
    public function registerVars($vars){
        $this->vars = $vars;
    }
    
    public function registerTags($tags){

        $this->tags['charset']     = CHARSET;
        $this->tags['autor']       = AUTOR;
        $this->tags['language']    = isset($_SESSION['language']) ? $_SESSION['language'] : "pt-br";
        $this->tags['description'] = SITE_DESCRIPTION;
        $this->tags['keywords']    = SITE_KEYWORDS;
        $this->tags['robots']      = "FOLLOW";
        $this->tags['page_title']  = SITE_NOME;
        $this->tags['favicon']     = URL_TEMPLATES . CURRENT_TEMPLATE . "/img/favicon.ico";

        //se vem alguma metatag por parametro
        if(!empty ($tags)){
            $this->tags = array_merge($this->tags, $tags);
        }
    }
    
    public final function enableAjax(){
        $this->ajax = true;
    }
    
    public function execute($view_name){
        $this->views['body'] = $view_name;
        if($this->ajax){
            echo "<html>";
            $this->LoadHead();
            echo "<body>";
            $this->load('body');
            $file = TEMPLATES."$this->template/mainscripts.php" ;
            if(file_exists($file))require $file;
            echo "</body>";
            $this->Html->flush(true);
            echo "</html>";
            
        }else {
            $this->LoadTemplate();
            $this->Html->flush(true);
        }
    }
    
    /*carrega o template com o nome passado pelo parâmetro*/
    private function LoadTemplate(){

        //verifica se arquivo existe
        $file = TEMPLATES."$this->template/$this->template"."Template.php" ;
        if(!file_exists($file)) throw new \classes\Exceptions\PageNotFoundException("O template $this->template não foi encontrado!");
        
        if(is_array($this->vars)){
            extract($this->vars);
        }
        //carrega o arquivo
        require_once ($file);
    }
    
    private function LoadHead($head = ""){
        if(is_array($this->vars))extract($this->vars);
        require_once TEMPLATES."config/head.php";
    }

    private function LoadRodape(){
        if(is_array($this->vars))extract($this->vars);
        require_once TEMPLATES."config/rodape.php";
    }
    
    private function getMenuPlugins(){
        $this->LoadModel('site/menu', 'menu');
        return $this->menu->getMenu();
    }
    
    private function LoadMessages(){
        if(is_array($this->vars))extract($this->vars);
        require_once TEMPLATES."config/messages.php";
    }
    
    private function load($view){
        if(!array_key_exists($view, $this->views))  return;
        if(is_array($this->vars)) extract($this->vars);
        $tmp  = $this->views[$view];
        $view = explode("/", $tmp);
        $module     = array_shift($view);
        $controller = array_shift($view);
        $view		= implode("/", $view);
        
        $file = MODULOS . "$module/$controller/views/$view"."View.html";
        try {
            if(file_exists($file))
                require_once $file ;
            else{
                $file = MODULOS . "$module/$controller/views/$view"."View.phtml";
                if(file_exists($file)){
                    require_once $file ;
                }
                else{
                    if(is_string($tmp) && file_exists($tmp)){
                        require_once $tmp; ;
                    }
                }
            }
        } catch (\Exception $exc) {
            echo "<div class='erro'>" .$exc->getMessage() . "</div>";
            if(DEBUG){
                echo "<hr>Arquivo:" .$exc->getFile() . "<br/>";
                echo "Linha:"   .$exc->getLine() . "<br/><hr/>";
            }
        }

        
    }
    
    private function loadTags($tag){
        if(array_key_exists($tag, $this->tags)){
            echo $this->tags[$tag];
        }
    }

    private function LoadRegion($region){
        $region = EventTube::getRegion($region);
        if(!$region) return false;
        foreach($region as $event){
            if(is_object($event)){
                $event->execute();
                continue;
            }
            if(is_array($event)){
                foreach($event as $nm => $str){
                    $id = GetPlainName($nm);
                    echo "<h2>$nm</h2><div class='$region-event' id='$id'>$str</div>";
                }
                continue;
            }
            echo "$event";
        }
    }
    
    private function evento($nome_evento){
    	$file = TEMPLATES . "$this->template/eventos/$nome_evento" . "Evento.php";
     	if(file_exists($file)){
    		require_once($file);
    		$class = $nome_evento . "Evento";
    		$evento = new $class();
    		$evento->draw();
    	}
    }
    
    private function LoadWidgets(){
        Widget::showAllWidgets($this->vars);
    }
    
    private function loadLoader($modulo){
        $class = $modulo."Loader";
        $file = MODULOS . "$modulo/Config/$class.php";
        if(!file_exists($file)) return;
        require_once $file;
        $menu_obj = new $class();
        $menu_obj->setCommonVars();
        return($menu_obj->getVars());
    }

}