<?php

namespace classes\FK;
class ShowFk1n extends ShowFkInterface{

    public function exibir(){
        $fkmodel    = $this->cont->getFkmodel();
        $var        = $this->cont->getVar();
        if(!is_array($var)) return;
        $keys       = array_keys($var);

        $link       = array_shift($keys);
        $valor      = array_shift($var);
        $url        = $this->Html->getActionLinkIfHasPermission("$fkmodel/show/$link", $valor);
        
        if($url != "")$this->cont->setVar($url);
    }
}

?>