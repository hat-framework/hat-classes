<?php
namespace classes\Component;
use classes\Classes\Object;
class tableFormComponent extends Object{
    
    private $model = NULL;
    public function __construct() {
        $this->LoadResource('formulario', 'form');
        $this->form = new FormHelper();
    }
    
    public function drawTableForm($dados, $action, $rows = array()){
        foreach($rows as $values){
            $this->form->Open($action, "", true);
            $this->form->setVars($values);
            return $this->form->Close();
        }
    }
    
}

?>