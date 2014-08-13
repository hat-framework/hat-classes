<?php

namespace classes\FK;
class ShowFk11 extends ShowFkInterface{

    public function exibir(){
        $fkmodel = $this->cont->getFkmodel();
        $var     = $this->cont->getVar();
        $this->LoadComponent($fkmodel, 'comp');
        $this->comp->show($fkmodel, $var);
        $this->cont->setVar("");
    }
}

?>