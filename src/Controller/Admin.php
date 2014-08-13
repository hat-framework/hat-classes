<?php

namespace classes\Controller;

class Admin extends \classes\Controller\Controller{
    
    public $model_name = LINK;
    public $autoaction = "";
    public function __construct($vars) {
        parent::__construct($vars);
        $this->registerVar("model"     , $this->model_name);
        $this->registerVar("insert_url", CURRENT_MODULE ."/".CURRENT_CONTROLLER."/inserir");
        $this->registerVar("view_url"  , CURRENT_MODULE ."/".CURRENT_CONTROLLER);
        if($this->model_name != ""){
            $this->LoadModel($this->model_name, "model", false);
        }
        $this->autoaction = 'admin/auto/custom';
        $this->ListMethods();
        
    }

    public function AfterLoad() {
        parent::AfterLoad();
        $this->cod = @$this->vars[0];
        if($this->cod != ""){
            if(method_exists($this->model, "getItem")) $this->item = $this->model->getItem($this->cod);
        }
    }
    
    private function ListMethods(){
        if(!function_exists('get_called_class')) return;
        $class         = get_called_class();   
        $class_methods = get_class_methods($class);
        $block         = get_class_methods("\classes\Controller\Admin");
        
        $var[$this->model_name."/index"]   = "Grid";
        $var[$this->model_name."/inserir"] = "Inserir";
        foreach ($class_methods as $method_name) {
            if(in_array($method_name, $block)) continue;
            $var[$this->model_name."/$method_name"] = ucfirst($method_name);
        }
        $this->registerVar("actions", $var);
    }
    
    public function index(){
        if($this->model_name == ""){
            $this->genTags("Opções do plugin " . ucfirst(CURRENT_MODULE));
            $this->display("admin/auto/indexAdmin");
        }else{
            $nome = CURRENT_CONTROLLER;
            $this->variaveis['actions'][$this->model_name."/gridfull"] = "Grid Fullscreen";
            unset($this->variaveis['actions'][$this->model_name."/index"]);
            if($nome == 'index') $nome = CURRENT_MODULE;
            $this->genTags("Widget ". ucfirst($nome));
            $this->display("admin/auto/index");
        }
    }
    
    public function gridfull(){
        $this->LoadResource('html', 'html');
        echo "<html><head></head><body>";
        $this->LoadComponent("admin/auto/action", 'act');
        unset($this->variaveis['actions'][$this->model_name."/gridfull"]);
        $this->act->draw($this->variaveis['actions']);

        $this->LoadResource("grid", "grid");
        $this->grid->execute("", $this->model_name);
        echo "</body></html>";
        $this->html->flush();
    }
    
    public function formulario(){
        $this->inserir();
    }
    
    public function inserir(){
        $nome = CURRENT_CONTROLLER;
        if($nome == 'index') $nome = CURRENT_MODULE;
        $this->genTags("Widget ". ucfirst($nome));
        
        $id = array_shift($this->vars);
        $status = "";
        if(!empty ($_POST)){
            if($id == "") {$status = $this->model->inserir($_POST); $this->genTags("Inserindo Dados");}
            else          {$status = $this->model->editar($id, $_POST); $this->genTags("Editando Dados"); }
            
            $id = $this->model->getLastId();
            $vars = $this->model->getMessages();
            if($status == true){
                $this->LoadResource('html', 'html');
                $vars['redirect'] = $this->html->getLink(LINK."/inserir/$id");
            }
            if(array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']) $vars['status'] = ($status == true)? 1:0;
            $this->setVars($vars);
        }
        
        $formulario = ($id == "")?"":$this->model->getItem($id);
        if(!array_key_exists('ajax', $_REQUEST) || !$_REQUEST['ajax']) $this->registerVar('formulario', $formulario);
        $this->display("admin/auto/inserir");
    }
    
    public function apagar(){
        $id = array_shift($this->vars);
        $this->model->apagar($id);
        $this->setVars($this->model->getMessages());
        $this->genTags("Apagar Dados");
        $this->display("admin/auto/apagar");
    }
    
}

?>