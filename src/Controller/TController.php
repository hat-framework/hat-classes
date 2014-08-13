<?php

namespace classes\Controller;
use classes\Classes\cookie;
class TController extends CController {

    public    $model_name = "";    
    protected $cod        = "";
    protected $urlcod     = "";
    protected $item       = array();
    public function AfterLoad(){
        //inicializa as variáveis
        if(isset($this->vars[0]) && isset($this->vars[1])){
            $this->cod = array($this->vars[0], $this->vars[1]);
            $this->urlcod = $this->vars[0] ."/". $this->vars[1];
            $this->registerVar('cod',  $this->urlcod);
        }
        if(in_array(CURRENT_ACTION, $this->free_cod)&& isset($_SESSION[LINK])) unset($_SESSION[LINK]);
        elseif(!empty ($this->cod))$_SESSION[LINK] = $this->cod;
        elseif(isset($_SESSION[LINK])) Redirect(CURRENT_URL ."/".  implode("/", $_SESSION[LINK]));
        
        if(!empty($this->cod) && !in_array(CURRENT_ACTION, $this->free_cod)){
            if(method_exists($this->model, 'getItem')){
                $this->item = $this->model->getItem($this->cod);
                if(empty($this->item)) {
                    $vars['erro'] = "Este item já foi apagado ou nunca existiu!";
                    $vars['status'] = "0";
                    Redirect (LINK, 0 , "", $vars);
                }
                
            }
        }
            
        //gera as tags
        if(!empty ($this->item)){
            $dados = $this->model->getDados();
            $resumo = $titulo = "";
            foreach($this->item as $name => $arr){
                if(!array_key_exists($name, $dados)) continue;

                $arr = $dados[$name];
                if(!array_key_exists('seo', $arr)) continue;
                if(array_key_exists("titulo", $arr['seo']) && $titulo == "") $titulo = $this->item[$name];
                if(array_key_exists("resumo", $arr['seo']) && $resumo == "") $resumo = $this->item[$name];
            }
            $this->genTags($titulo , $resumo, str_replace(" ", " ,", $titulo));
            $this->genImageTag($this->item);
        }

        $this->registerVar("item", $this->item);
        if(cookie::cookieExists($this->sess_cont_alerts)){
            $this->setVars(cookie::getVar($this->sess_cont_alerts));
            cookie::destroy($this->sess_cont_alerts);
        }
    }
    /*
    public function formulario($display = true, $link = "") {
        parent::formulario(false);
        parent::show();
    }*/

}