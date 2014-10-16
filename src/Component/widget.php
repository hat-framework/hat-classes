<?php

namespace classes\Component;
class widget extends \classes\Classes\Object{

    protected $pgmethod  = "paginate";
    protected $method    = "listInTable";
    protected $modelname = "";
    protected $arr       = array();
    protected $link      = "";
    protected $where     = "";
    protected $qtd       = "";
    protected $page      = 0;
    protected $order     = "";
    protected $title     = "";
    protected $class     = "span12";
    protected $id        = "";
    protected $component = null;
    protected $model     = null;
    protected $actionPaginator = "";
    protected $showCount = false;
    protected $countTitle = "Total";
    protected $drawEmpty = true;
    protected $cachename = "";

    protected $gui = null;
    public function __construct() {
        $this->LoadResource('html', 'Html');
        $this->gui = new GUI();
    }
    
    public function init(){
        if(!is_object($this->model))                                                         $this->setModel($this->modelname);
        if(is_object($this->component) && !method_exists($this->component, $this->method))   $this->method = "listInTable";
        if(is_object($this->model)     && !method_exists($this->model    , $this->pgmethod)) $this->pgmethod = "paginate";
        $this->findPage();
    }
    
    private function findPage(){
        $widget = filter_input(INPUT_GET, 'widget');
        if($widget == "" || $widget != self::whoAmI()) {return;}
        $page = filter_input(INPUT_GET, 'wd_page');
        if($page == "") {return;}
        $this->setPage(str_replace("/", '', $page));
    }
    
    protected function getItens(){
        $this->mountWhere();
        if(!is_object($this->model)) return array();
        $pgmethod   = $this->pgmethod;
        $action   = $this->getLinkPaginator();
        return $this->model->$pgmethod(
                $this->page,
                $action, "", "", 
                $this->qtd, 
                $this->arr, 
                $this->where, 
                $this->order
        );
    }
    
    protected function mountWhere(){}


    private function getLinkPaginator(){
        $action     = $this->actionPaginator;
        if($action == ""){
            $action = $this->Html->getLink(CURRENT_URL, true, true);
            $action.= "&widget=".self::whoAmI();
            $action.= "&wd_page=";
        }
        return $action;
    }
    
    public function widget(){
        $this->init();
        $this->drawDropper();
        if($this->drawCache()) {
            return;
        }
        $itens = $this->getItens();
        $this->draw($itens);
    }
    
    private function drawDropper(){
        if($this->cachename === "" || !\usuario_loginModel::IsWebmaster()) return;
        $url = URL. "index.php?url=site/index/cache&file={$this->cachename}.html&action=drop";
        echo "<a href='$url' target='_BLANK' class='pull-right' style='position:relative'><i class='fa fa-trash-o'></i></a>";
    }
    
    private function drawCache(){
        if($this->cachename == "") return false;
        if(\classes\Utils\cache::exists($this->cachename)){
            echo \classes\Utils\cache::get($this->cachename);
            return true;
        }
        $itens = $this->getItens();
        ob_start();
        $this->draw($itens);
        $flush = ob_get_contents();
        ob_end_clean();
        if(trim($flush) == "") return true;
        \classes\Utils\cache::create($this->cachename, $flush);
        echo $flush;
        return true;
    }
    
    protected function draw($itens){
        if(empty($itens) && !$this->drawEmpty) return '';
        $this->openWidget();
            if($this->showCount){
                $this->LoadResource("html/paginator", 'pag');
                echo "$this->countTitle: ". $this->pag->getLastCount() . "<br/>";
            }
            $this->listMethod($itens);
        $this->closeWidget();
    }
    
    protected function listMethod($itens){
        if(!is_object($this->component)) return;
        $listMethod = $this->method;
        $this->component->$listMethod($this->modelname, $itens);
    }


    protected function getLinks(){
        if($this->link == "") return;
        if(!is_array($this->link)) $this->link = array($this->link => "Criar novo");
        $str = "";
        foreach($this->link as $url => $name){
            $link = $this->Html->getLink($url);
            $str .= "<a href='$link' class='btn btn-success'>$name</a>";
        }
        return $str;
    }


    public function setPaginateMethod($method){
        $this->pgmethod = $method;
        return $this;
    }
    
    public function setListMethod($method){
        $this->method = $method;
        return $this;
    }
    
    public function setModel($model){
        if(is_object($model)){
            $this->model     = $model;
            $this->modelname = str_replace("_", "/", $this->model->getModelName());
        }elseif($model != ""){
            $this->modelname = $model;
            $this->LoadModel($model, 'model');
        }
        if($this->modelname != ""){
            $this->component = $this->LoadComponent($this->modelname, 'comp');
            //if($this->link == "") $this->setPageLink('formulario');
        }
        return $this;
    }
    
    public function setSelectedVars($array){
        if(!is_array($array)) die("a arr deve ser um array!");
        $this->arr = $array;
        return $this;
    }
    
    public function setPageLink($link){
        $this->link = "$this->modelname/$link";
        return $this;
    }
    
    public function setPage($page){
        $this->page = $page;
        return $this;
    }
    
    
    public function setWhere($where){
        $this->where = $where;
        return $this;
    }
    
    public function setQtd($qtd){
        $this->qtd = $qtd;
        return $this;
    }
    
    public function setOrder($qtd){
        $this->order = $qtd;
        return $this;
    }
    
    public function setClass($class){
        $this->class = $class;
        return $this;
    }
    
    public function setId($id){
        $this->id = $id;
        return $this;
    }
    
    public function setTitle($title){
        $this->title = $title;
        return $this;
    }
    
    public function setAction($actionPaginator){
        $this->actionPaginator = $actionPaginator;
        return $this;
    }
    
    public static function executeWidgets($widgets){
        if(empty($widgets)) return;
        $obj = new \classes\Classes\Object();
        foreach($widgets as $classname => $options){
            $widget = $obj->LoadClassFromPlugin($classname, 'prod', false);
            if(!empty($options)){
                foreach($options as $method => $setvalue){
                    if(!method_exists($widget, $method)){
                        $method = "set".ucfirst($method);
                    }
                    if(method_exists($widget, $method)){
                        $widget->$method($setvalue);
                    }
                }
            }
            $widget->widget();
        }
    }
    
    public function openWidget($id = "", $adicional_title = ''){
        $class = "widget_".  GetPlainName(self::whoAmI());
        if($id == "") {$id = ($this->id == "")?$class:$this->id;}
        $this->gui->opendiv($id, "$this->class $class __widget");
                $this->gui->subtitle($this->title);
                echo $adicional_title;
            echo $this->getLinks();
            $this->gui->opendiv('', "widget_content");
    }
    
    public function closeWidget(){
            $this->gui->closediv();//widget_content
        $this->gui->closediv();//widget
    }
}
