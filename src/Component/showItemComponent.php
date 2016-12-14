<?php

namespace classes\Component;
use classes\Classes\Object;

class showItemComponent extends Object{
    
    private $component               = null;
    private $append_name             = ''; 
    private $showlabel               = '';
    private $drawInTable             = false;
    private $tbContent               = array();
    private $tbContentTitle          = array();
    private $fkn1data                = array();
    private $show_item_content_class = "";
    public function __construct($component) {
        $this->component = $component;
        $this->sfk = new \classes\FK\ShowFk();
        $this->gui = new GUI();
    }
    
    public function setAppendName($append_name){
        $this->append_name = $append_name; 
    }
    
    public function setShowlabel($showlabel){
        $this->showlabel = $showlabel; 
    }
    
    public function setShow_item_content_class($show_item_content_class){
        $this->show_item_content_class = $show_item_content_class; 
    }
    
    private function checkItem($model, $item){
        if(!is_array($item) ||empty ($item)) return false;
        if(!$this->pode_exibir($model, $item)){
            $this->conteudo_bloqueado();
            return false;
        }
        return true;
    }
    
    public function loadDados($model){
        if(!empty($this->dados)){return $this->dados;}
        $this->LoadModel($model, 'obj');
        $dados = $this->obj->getDados();
        if(empty ($dados)) throw new \classes\Exceptions\ComponentException(
            __CLASS__, 
            "O Modelo $model não foi configurado corretamente e está vazio"
        );
        $this->dados = $dados;
        return $dados;
    }
    
    public function enableTablePrint(){
        $this->drawInTable = true;
        return $this;
    }
    public function disableTablePrint(){
        $this->drawInTable = false;
        return $this;
    }


    public function show($model, $item){
        if(!$this->checkItem($model, $item)){return;}
        $this->loadDados($model);
        
        $id = str_replace("/", "_", $model);
        $this->gui->opendiv($id, "col-xs-12");
        $this->component->drawTitle($item);
        $this->gui->openPanel('panel_item')
                  ->panelHeader("Dados")
                  ->panelBody($this->printData($item))
                  ->closePanel();
        $this->printFkn1();
        $this->gui->closediv()
                  ->clear();
    }
    
            private function printData($item){
                foreach($item as $name => $var){
                    $this->drawItem($name, $var, $item);
                }
                $this->printTable();
            }
            
            private function printFkn1(){
                foreach($this->fkn1data as $data){
                    $this->gui->openPanel('panel_item')
                              ->panelHeader(isset($data['dados']['name'])?$data['dados']['name']:"")
                              ->panelBody($this->sfk->exibir($data['dados'], $data['var'], $data['name']))
                              ->closePanel()
                              ->closediv()
                              ->clear();
                    
                }
            }
    
            private function printTable(){
                if(empty($this->tbContent)){return;}
                $out = array();
                foreach($this->tbContent as $name => $val){
                    $nm = (array_key_exists($name, $this->tbContentTitle))?$this->tbContentTitle[$name]:$name;
                    $out[] = array($nm, $val);
                }
                $this->LoadResource('html/table', 'tb')->draw($out);
            }
            
            private function drawItem($name, $var, $item){
                $dados = $this->dados;
                $arr   = $this->getArr($name, $dados, $item, $var);
                if($arr === false){return;}
                $nn    = str_replace('__', '', $name);
                $type  = isset($dados[$nn]['type'])?$dados[$nn]['type']:"";
                $this->DrawShowItem($name, $var, $arr, $item, $type);
            }
                    private function getArr($name, $dados, $item, &$var){
                        if(!array_key_exists($name, $dados)){return array();}
                        $arr = $dados[$name];
                        $method = 'format_'.$name;
                        if(method_exists($this->component, $method)){
                            $var = $this->component->$method($var, $arr, $item);
                            $arr['type'] = '';
                            return $arr;
                        }
                        
                        if(!array_key_exists("fkey", $arr)){return $arr;}
                        if($arr['fkey']['cardinalidade'] !== 'n1'){
                            $var = $this->sfk->exibir($arr, $var, $name);
                            return $arr;
                        }
                        $this->fkn1data[] = array('name'=>$name, 'dados' => $arr, 'item'=>$item, 'var'=>$var);
                        return false;
                    }

    public function pode_exibir($model, $item){
        $exibir = "__".str_replace("/", "_", $model) . "_exibir";
        if(!array_key_exists($exibir, $item)) return true;
        if($item[$exibir] != 'esconder') return true;
        
        $this->LoadModel('usuario/login', 'uobj');
        if(!$this->uobj->IsLoged()) return false;
        if(!$this->uobj->UserIsAdmin()) return false;
        $this->gui->info("Este conteúdo está bloqueado para a exibção ");
        return true;
    }
    
    public function conteudo_bloqueado(){
        echo "<hr />";
        $this->gui->title("Conteúdo bloqueado");
        $this->gui->subtitle("Este conteúdo foi bloqueado pelo administrador para edição.");
        echo "<hr />";
    }
    
    public function DrawShowItem($name, $item_value, $dados, $item, $classname){
        if(!$this->canShow($name, $dados)) {return;}
        $value = $this->component->formatType($name, array($name=>$dados), $item_value, $item);
		if($value == "" || empty($value)) {return;}
		//formatType($name, $dados, $valor, $item = array())
        $__class = $this->component->getShowItemClass($name);
        $this->drawType("<span id='$name' class='$__class c_item $classname'>");
        $this->drawLabel($dados, $name);
        $this->drawContent($name, $value, $dados, $item, $classname);
        $this->drawType("</span>");
    }
    
            private function canShow($name, $arr){
                if($this->checkIsPrivate(array($name => $arr), $name)) {return false;}
                if(@$name[0] == "_" && @$name[1] == "_") {return false;}
                return true;
            }
            
                    public function checkIsPrivate($dados, $name){
						if(@$name[0] == "_" && @$name[1] == "_") {return true;}
						if(!array_key_exists($name, $dados)) {return false;}
						if(!is_array($dados[$name])) {return true;}
						if(array_key_exists('private', $dados[$name]) && $dados[$name]['private'] == true ) {return true;}
						if(array_key_exists('mobile_hide', $dados[$name]) && $dados[$name]['mobile_hide'] == true && MOBILE == true) {return true;}
                        return false;
                    }
    
            public function drawLabel($arr, $name){
                if(!$this->showlabel) {return;}
                if(isset($arr['hidelabel']) && $arr['hidelabel'] == true) {return;}
                $label = (array_key_exists('name', $arr))?$arr['name']:ucfirst ($name);
                $this->drawLabelType("<h4 class='label_title'>$this->append_name $label</h4>", $name, "$this->append_name $label");
                $this->append_name = "";
            }
            
            private function drawContent($name, $var, $arr, $item, $classname){
                $method = "format_$name";                
                if(method_exists($this, $method)) {
                    $var = trim($this->$method($var, $arr, $item));
                    $this->drawType("<span class='$classname'>$var</span>", $name, $var);
                    return;
                }
                if(!is_array($var)){
                    $this->drawType("<span class='$classname $this->show_item_content_class'>$var</span>", $name, $var);
                    return;
                }
                $s = "";
                $t = "";
                foreach($var as $v){
                    $s .= "$t$v";
                    $t = " - ";
                }
                $this->drawType($s, $name, $s);
            }
            
    private function drawType($str, $name = '', $table_content = ''){
        if(!$this->drawInTable){
            echo $str;
            return;
        }
        if(trim($name) === "" || trim($table_content) === ''){return;}
        $this->tbContent[$name] = $table_content;
    }
    
    private function drawLabelType($str, $name, $label){
        if(!$this->drawInTable){
            echo $str;
            return;
        }
        if(trim($name) === "" || trim($label) === ''){return;}
        $this->tbContentTitle[$name] = $label;
    }
}