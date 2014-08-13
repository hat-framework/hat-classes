<?php

namespace classes\FK;
class ShowFknn extends ShowFkInterface{

    public function exibir(){
        $var        = $this->cont->getVar();
        $fkmodel    = $this->cont->getFkmodel();
        $k2         = $this->cont->getK2();
        $name       = $this->cont->getName();
        
        $obj = "{$name}obj";
        $dt0 = "{$name}dt0";
        $dt1 = "{$name}dt1";
        if(!isset($this->$obj)){
            $this->LoadModel($fkmodel, $obj);
            $dt = $this->$obj->getPkey();
            $this->$dt0 = array_shift($dt);
            $this->$dt1 = array_shift($dt);
        }
        $virg = $out = "";
        //$this->append_name = $this->getFkeyLink($fkmodel, $dados[$name]['fkey']);
        if(!empty($var)){
            foreach($var as $link_show => $v){
                $key_link = '';
                if(array_key_exists($this->$dt0, $v) && array_key_exists($this->$dt1, $v)){
                    $key_link = $v[$this->$dt0]."/".$v[$this->$dt1];
                }

                //$cod    = $v[$k1];
                $nm     = $v[$k2];
                //die("$md_link - $fkmodel");
                $url       = "$fkmodel/show/$key_link/".GetPlainName($nm);
                $link_show = $this->Html->getActionLinkIfHasPermission($url, $nm);
                //$link_drop = ($key_link != "")?$this->Html->getActionLinkIfHasPermission("$fkmodel/apagar/$key_link/", "[X]", 'apagar_nn'):"";
                $link_drop = "";
                if($link_show == "") {
                    $virg      = ($virg == " ")?"-":'';
                    $link_show = $nm;
                }

                $out   .= "<span class='act_link_container'>| $link_show $link_drop</span>";
            }
            $out .= " |";
        }
        $this->cont->setVar($out);
    }
}

?>