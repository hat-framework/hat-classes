<?php

namespace classes\FK;
class ShowFkn1 extends ShowFkInterface{

    public function exibir(){
        $this->load();
        $arr     = $this->cont->getArr();
        $var     = $this->cont->getVar();
        $fkmodel = $this->cont->getFkmodel();
        if(array_key_exists('display_in', $arr) && ($arr['display_in'] == 'table')){
            $this->comp->listInTable($fkmodel, $var);
        }else{$this->comp->listar($fkmodel, $var);} //$this->comp->listar($fkmodel, $var);
        $this->cont->setVar("");
    }
        
    private function load(){
        $var     = $this->cont->getVar();
        $fkmodel = $this->cont->getFkmodel();
        $arr     = $this->cont->getArr();
        
        $this->LoadComponent($fkmodel, 'comp');
        if(empty($var)){return;}
        $name = $this->cont->getName();
        $this->cbox();
        //if($card == 'n1') $this->append_name = $this->Html->getActionLinkIfHasPermission("$fkmodel/formulario/ajax", "[+]", "addnew");
        $this->comp->drawLabel($arr, $name);
    }
    
    private function cbox(){
        if(isset($this->cbox)){return;}
        $this->LoadResource('jqueryui/dialogs','cbox');
        $this->cbox->ajaxDialog('.addnew');
        
    }
    
}