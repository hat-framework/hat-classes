<?php

namespace classes\Controller;
use classes\Classes\session;
class TController extends CController {

    public    $model_name = "";    
    protected $cod        = "";
    protected $urlcod     = "";
    protected $item       = array();
    public function AfterLoad(){
        //inicializa as variáveis
        $url   = substr(CURRENT_PAGE, 0, strlen(CURRENT_PAGE)-1);
        if(!in_array(CURRENT_ACTION, $this->free_cod) && $this->act->needCode($url)){
            $pkeys     = $this->model->getPkey();
            $this->cod = array();
            foreach($pkeys as $i => $pk){
                if(!isset($this->vars[$i])){Redirect("");}
                $this->cod[] = $this->vars[$i];
            }
            $this->urlcod = implode('/',$this->vars);
            $this->registerVar('cod',  $this->urlcod);
            $this->manageSessions();
            $this->prepareItem();
            $this->generateItemTags();
            return $this->registerItem();
        }
        $this->addToFreeCod(CURRENT_ACTION);
    }
    
    public function dataList(){
        $page  = isset($this->vars[1])?$this->vars[1]:0;
        $this->registerVar('item', $this->model->listSide($this->vars[0], $page));
        $this->registerVar("comp_action" , $this->getIndexListType());
        $this->registerVar("show_links"  , '');
    	$this->display('admin/auto/areacliente/page');
    }

}
