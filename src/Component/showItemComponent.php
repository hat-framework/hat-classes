<?php

namespace classes\Component;
use classes\Classes\Object;

class showItemComponent extends Object{
    
    private $component   = null;
    private $append_name = ''; 
    private $showlabel   = '';
    private $show_item_content_class = "";
    public function __construct($component) {
        $this->component = $component;
        $this->sfk = new \classes\FK\ShowFk();
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
    
    private function drawItem($name, $var, $item){
        $dados = $this->dados;
        if(array_key_exists($name, $dados)){
            $arr = $dados[$name];
            $method = 'format_'.$name;
            if(method_exists($this->component, $method)){
                $var = $this->component->$method($var, $arr, $item);
                $arr['type'] = '';
            }
            elseif(array_key_exists("fkey", $arr)){
                $var = $this->sfk->exibir($arr, $var, $name);
            }
        }else $arr = array();

        $nn   = str_replace('__', '', $name);
        $type = isset($dados[$nn]['type'])?$dados[$nn]['type']:"";
        $this->DrawShowItem($name, $var, $arr, $item, $type);
    }
    
    public function show($model, $item){
        if(!$this->checkItem($model, $item)){return;}
        $this->loadDados($model);
        
        $id = str_replace("/", "_", $model);
        echo "<div id='$id'>"; 
            $this->component->drawTitle($item);
            foreach($item as $name => $var){
                $this->drawItem($name, $var, $item);
            }
        echo "</div><div class='clear'></div>";
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
    
    private function formatType($arr, &$var){
        if(array_key_exists('type', $arr)){
            $type = $arr['type'];
            switch ($type){
                case 'date':
                    if($var == '0000-00-00' || $var == '0000-00-00 00:00:00'){ $var = ""; }
                    else $var = \classes\Classes\timeResource::Date2StrBr($var, false);
                    break;
                case 'datetime': 
                case 'timestamp': 
                    if($var == '0000-00-00' || $var == '0000-00-00 00:00:00'){ $var = ""; }
                    else $var = \classes\Classes\timeResource::Date2StrBr($var);
                    break;
                case 'bit': 
                    $var = ($var == 1 || $var === true)?"Sim":"Não";
                    break;
            }
        }
        if(!is_array($var)) $var = trim($var);
        if($var == "" || empty($var)) {return false;}
        return true;
    }
    
    private function canShow($name, $arr){
        if($this->checkIsPrivate(array($name => $arr), $name)) {return false;}
        if(@$name[0] == "_" && @$name[1] == "_") {return false;}
        return true;
    }
    
    private function drawContent($name, $var, $arr, $item, $classname){
        $method = "format_$name";                
        if(method_exists($this, $method)) {
            $var = trim($this->$method($var, $arr, $item));
            echo "<span class='$classname'>$var</span>";
            return;
        }
        if(!is_array($var)){
            echo "<span class='$classname $this->show_item_content_class'>$var</span>";
            return;
        }
        $t = "";
        foreach($var as $v){
            echo "$t$v";
            $t = " - ";
        }
    }
    
    public function DrawShowItem($name, $var, $arr, $item, $classname){
        if(!$this->canShow($name, $arr)) {return;}
        if(!$this->formatType($arr, $var)) {return;}
        $__class = $this->component->getShowItemClass($name);
        echo "<span id='$name' class='$__class c_item $classname'>";
        $this->drawLabel($arr, $name);
        $this->drawContent($name, $var, $arr, $item, $classname);
        echo "</span>";
    }
    
    public function checkIsPrivate($dados, $name){
        if(@$name[0] == "_" && @$name[1] == "_") return true;
        if(!array_key_exists($name, $dados)) return false;
        if(!is_array($dados[$name])) return true;
        if(array_key_exists('private', $dados[$name]) && $dados[$name]['private'] == true ) return true;
        if(array_key_exists('mobile_hide', $dados[$name]) && $dados[$name]['mobile_hide'] == true && MOBILE == true) return true;
        return false;
    }
    
    public function drawLabel($arr, $name){
        if(!$this->showlabel) return;
        if(isset($arr['hidelabel']) && $arr['hidelabel'] == true) return;
        $label = (array_key_exists('name', $arr))?$arr['name']:ucfirst ($name);
        echo "<h4 class='label_title'>$this->append_name $label</h4>";
        $this->append_name = "";
    }
}