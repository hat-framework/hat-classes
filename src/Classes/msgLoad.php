<?php
namespace classes\Classes;
use classes\Classes\Object;
use classes\Classes\EventTube;
class msgLoad extends Object{
    protected $page    = 0;
    protected $qtd     = 10;
    protected $model   = "";
    protected $link    = "";
    protected $pkey    = '';
    protected $pagtype = 'multipage'; //singlepage
    private   $tb      = '';
    private   $pkmodel = '';
    
    public function __construct() {
        $this->LoadResource('html/paginator', 'pag');
        $this->LoadModel($this->model, 'md');
        $this->tb      = $this->md->getTable();
        $this->pkmodel = $this->md->getPkey();
    }
    
    public function setPage($page){
        if(!is_numeric($page) || $page < 0)$page = 0;
        $this->page = $page;
    }
    
    public function setQtd($qtd){
        if(!is_numeric($qtd) || $qtd < 5)$qtd = 5;
        $this->qtd = $qtd;
    }
    
    public function getPage(){
        return $this->page;
    }
    
    public function getQtd(){
        return $this->qtd;
    }
    
    public function LoadExtraData($cod){
        //$this->pag->startDebug();
        $this->pag->setPaginationType($this->model, $this->pagtype);
        $var = $this->md->paginate($this->page, "$this->link/$cod/", "", "", $this->qtd, array(), 
                "$this->tb.$this->pkey = '$cod'", "$this->tb.$this->pkmodel DESC");
        //EventTube::Debug();
        return $var;
    }
    
    public function updateData($cod, $lastid){
        $this->pag->setPaginationType($this->model, $this->pagtype);
        $var = $this->md->paginate($this->page, "", "", "", $this->qtd, array(), 
                "(($this->tb.$this->pkey = '$cod') AND ($this->tb.$this->pkmodel > $lastid))", "$this->tb.$this->pkmodel DESC");
        $this->showList($var);
    }
    
    public function LoadPage($cod){
        $this->pag->setPaginationType($this->model, $this->pagtype);
        $var = $this->md->paginate($this->page, "$this->link/$cod/", "", "", $this->qtd, array(), 
                "($this->tb.$this->pkey = '$cod')", "$this->tb.$this->pkmodel DESC");
        $this->showList($var);
    }
    
    private function showList($var){
        if(empty($var)) return;
        $this->LoadComponent($this->model, 'comp');
        $this->comp->showOnlyListItem();
        $this->comp->listar($this->model, $var);
    }
}

?>