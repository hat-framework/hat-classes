<?php
namespace classes\Component;
if(!defined('MAX_STR_IN_LIST')) define('MAX_STR_IN_LIST', 120);
use classes\Classes\Object;
use classes\Classes\EventTube;
use timeResource;
class Component extends Object{
    
    protected $showlabel               = true;
    public    $list_in_table           = false;
    protected $append_name             = "";
    protected $gui                     = null;
    protected $append_header           = "";
    protected $prepend_header          = "";
    protected $show_item_class         = 'c_description';
    protected $show_item_title_class   = '';
    protected $show_item_content_class = '';
    //protected $listActions = array('Veja Mais' => "show", 'Editar' => "edit", 'Excluir' => "apagar");
    protected $listActions = array('Veja Mais' => "show");
    public function __construct() {
        $this->LoadResource("html", "Html");
        $this->LoadResource('jqueryui/dialogs','cbox');
        $this->gui = new \classes\Component\GUI();
        static $foo = 0;
        if($foo == 0) $this->js();
        $foo = 1;
    }
    
    public function addListAction($name, $link){
        $this->listActions[$name] = $link;
    }
    
    public function removeListAction($name = ""){
        if($name != "" && isset($this->listActions[$name])){
            unset($this->listActions[$name]);
            return;
        }
        $this->listActions = array();
    }
    
    protected function LoadCss($model){
        
        $temp = explode("/", $model);
        $csss[] = str_replace("_", "/", $model);
        $csss[] = array_shift($temp);
        $csss[] = 'component';
        foreach($csss as $css)
            if($this->Html->loadCssIfExists($css) !== false) return $css;
    }

    protected $showOnlyListItem = false;
    public function showOnlyListItem(){
        $this->showOnlyListItem = true;
    }

    protected $dados = array();
    protected $itens = array();
    public function listar($model, $itens, $title = "", $class = ''){
        $this->itens = $itens;
        if($this->list_in_table){
            $this->listInTable($model, $itens, $title, $class);
            return;
        }
        $this->LoadModel($model, 'obj');
        $dados = $this->obj->getDados();
        $this->dados = $dados;
        
        $addclass = $this->getEnumClasses($dados);
        $pkey = $this->obj->getPkey();
        $id = str_replace("/", "_", $model);
        $class = ($class != "")? " class='$class'":"";
        $display = (is_array($itens) && !empty ($itens));
        if(!$this->showOnlyListItem){
            echo "<div$class> ";
                if(!empty($itens)) $this->gui->subtitle($title);
                echo "<ul id='$id' class='list'>";
        }
        if($display){
                foreach($itens as $item){
                    if(!$this->pode_exibir($model, $item)) continue;
                    $class = $this->getEnumClass($addclass, $item);
                    echo "<li class='list-item list_$id $class'>";
                        $this->DrawItem($model, $pkey, $item, $dados);
                    echo "</li>";
                }
        }
        
        $this->print_paginator_if_exists($this->obj);
        if(!$this->showOnlyListItem){
                echo "</ul>";
            echo "</div>";     
        }
        $this->itens = array();
    }
    
    protected function getEnumClasses($dados){
        $addclass = array();
        foreach($dados as $nm => $d){
            if(!is_array($d)) continue;
            if(array_key_exists('type', $d) && $d['type'] == 'enum'){
                $addclass[] = $nm;
            }
        }
        return $addclass;
    }
    
    protected function getEnumClass($addclass, $item){
        $class = "";
        if(!empty($addclass)){
            foreach($addclass as $var){
                if(array_key_exists($var, $item)){
                    $class .= " ". strip_tags($item[$var]);
                }
            }
        }
        return $class;
    }
    
    protected function print_paginator_if_exists($obj){
        $model = $obj->getTable();
        $v = EventTube::getRegion("paginate_$model");
        EventTube::clearRegion("paginate_$model");
        if(empty($v)) return;
        $v = array_shift($v);
        echo $v;
    }

    private $keeptags = false;
    public function keepTags(){
        $this->keeptags = true;
    }

    public function listInTable($model, $itens, $title = "", $class = '', $drawHeaders = false, $header = array()){
        
        $this->LoadModel($model, 'obj');
        $dados = $this->obj->getDados();
        $pkey = $this->obj->getPkey();
        $id = str_replace("/", "_", $model);
        $class = ($class != "")? " class='$class'":"";
        $addclass = $this->getEnumClasses($dados);
        $table  = array();
        $i      = 0;
        
        $stopheader = false;
        if(!empty($itens)){
            foreach($itens as $item){
                $tb = array();
                if(!$this->pode_exibir($model, $item)) continue;
                foreach($item as $name => $valor){
                    if($this->checkIsPrivate($dados, $name)) continue;
                    if(!$stopheader) $header[] = array_key_exists($name, $dados)?$dados[$name]['name']:$name;
                    $val = $this->formatType($name, $dados, $valor, $item);
                    $tb[$name] = $val;
                }
                $links = $this->getActionLinks($model, $pkey, $item);
                //if(!MOBILE){
                    $tb["Ações"] = (is_array($links))? implode(" ", $links):"";
                    if(!$stopheader) {
                        if($tb["Ações"] != "" && !MOBILE){
                            $header[] = "Ações";
                        }
                    }
                    if($tb["Ações"] == "") unset($tb["Ações"]);
                //}
                //else $tb['__link'] = $links;
                
                $tb["__id"]  = implode("-", $this->getPkeyValue($pkey, $item));
                $tb["__class"] = $this->getEnumClass($addclass, $item);
                
                $table[$i] = $tb;
                $i++;
                $stopheader = true;
            }
        }else{
            //getHeader
            foreach($dados as $name => $arr){
                if($this->checkIsPrivate($dados, $name)) continue;
                if(!isset($arr['name'])) continue;
                if(!isset($arr['display'])) {continue;}
                $header[] = $arr['name'];
            }
            if(!empty($this->listActions)){
                $header[] = "Ações";
            }
        }
        echo "<div$class id='$id'> ";
            $this->gui->subtitle($title);
            $this->LoadResource('html/table', 'tb');
            $this->tb->forceDrawHeaders();
            $this->tb->printable(true);
            $this->tb->draw($table, $header);
            $this->print_paginator_if_exists($this->obj);
        echo "</div>";
    }
    
    protected function DrawItem($model, $pkey, $item, $dados){
        
        if(empty ($item)) return;
        if(!$this->pode_exibir($model, $item)) return;
        $links = $this->getActionLinks($model, $pkey, $item);
        $id = "";
        if(is_array($pkey)){
            $v = "";
            foreach($pkey as $pk){
                if(!array_key_exists($pk, $item)) continue;
                $id .= $v.$item[$pk];
                $v  = "-";
            }
        }elseif(array_key_exists($pkey, $item)) $id = $item[$pkey];
        $id   = ($id != "")?" id='$id'":'';
        $lini = $linf = "";
        //if(MOBILE){
        //    $url = $this->Html->getLink($links);
        //    $lini = "<a href='$url'>";
        //    $linf = "</a>";
        //    $link = "";
        //}
        //else 
        $link  = (is_array($links))? implode(" ", $links):"";
        
        echo "<div class='container'$id>$lini";
        foreach($item as $name => $it){
            if($this->checkIsPrivate($dados, $name)) {continue;}
            $it = $this->formatType($name, $dados, $it, $item);
            if(is_array($it) && isset($dados[$name]['fkey'])){
                 $this->LoadComponent($dados[$name]['fkey']['model'], 'md');
                 $this->md->listar($dados[$name]['fkey']['model'], $it);
            }elseif(!is_array($it)){
                echo "<span class='$name'>$it</span>";
            }
            else $this->show($model, $it);
        }
        echo "$link $linf </div>";
    }
    
    protected function getActionLinks($model, $pkey, $item){
        $pkey = implode("/",$this->getPkeyValue($pkey, $item));
        $v = array();
        foreach($this->listActions as $name => $action){
            $link = $this->getActionOfLink($name, $action, $model, $pkey);
            if($link != "") $v[$name] = $link;
        }
        return($v);
    }
    
    private function getActionOfLink($name, $action, $model, $pkey){
        $class = $url = "";
        if(strstr($action, "/") === false){
            $class = GetPlainName($action);
            $url = "$model/$action/$pkey";
        }else{
            $cl    = explode("/", $action);
            while(!empty($cl) && is_numeric(end($cl))){array_pop($cl);}
            $class = end($cl);
            $url   = "$action/$pkey";
        }
        return $this->Html->getActionLinkIfHasPermission($url, "$name",$class, "");
    }
    
    protected function gerarLink($model, $pkey, $item){
        $pkey = implode("/",$this->getPkeyValue($pkey, $item));
        return $this->Html->getLink("$model/show/$pkey");
    }
    
    protected function getPkeyValue($pkey, $item){
        $out = array();
        if(is_array($pkey)){
            foreach($pkey as $pk){
                if(isset($item["__$pk"])) $out[] = strip_tags ($item["__$pk"]);
                elseif(isset($item[$pk])) $out[] = strip_tags ($item[$pk]);
            }
        }elseif(isset($item[$pkey])) $out[] = strip_tags ($item[$pkey]);
        return $out;
    }
    
    public function formatType($name, $dados, $valor, $item = array()){
        $method = "format_$name";
        if(method_exists($this, $method)) {
            return trim($this->$method($valor, $dados, $item));
        }
        if(!array_key_exists($name, $dados)) {if(!is_array ($valor))return trim($valor); else return $valor;}
        if(!array_key_exists('type', $dados[$name])){
            return is_array($valor) ? $valor: trim($valor);
        }
        if(is_array($valor)) return $valor;
        switch ($dados[$name]["type"]){
            case 'date':
                if($valor == '0000-00-00') return "";
                $valor = \classes\Classes\timeResource::getFormatedDate($valor);
                break;
            case 'datetime': 
            case 'timestamp': 
                if($valor == '0000-00-00' || $valor == '0000-00-00 00:00:00') return "";
                $valor = \classes\Classes\timeResource::getFormatedDate($valor);
                break;
            case 'time': 
                if($valor == '00:00:00' || $valor == '00:00') return "";
                break;    
            case 'text':
            case 'varchar':
                if(!$this->keeptags){
                    $valor = strip_tags($valor, "<b><a><ul><li><ol><i><u>");
                    if(strlen($valor) <= MAX_STR_IN_LIST) break;
                    $valor = Resume($valor, MAX_STR_IN_LIST);
                }
                break;
            case 'bit': 
                $valor = ($valor == 1 || $valor === true)?"Sim":"Não";
                break;
            case 'enum':
                $valor = (isset($dados[$name]['options'][$valor]))?$dados[$name]['options'][$valor]: ucfirst($valor);
                break;
            case 'decimal':
                if(!is_numeric($valor)) break;
                if(!isset($dados[$name]['size'])) break;
                $e = explode(',', $dados[$name]['size']);
                $casas = end($e);
                if($casas == "") $casas = 2;
                $valor = number_format($valor, $casas, ',', '.');
                break;
        }
        return trim($valor);
    }
    
    public function form($model, $values = array(), $ajax = true, $url = ""){
        $this->LoadResource('formulario', 'form');
        $url = ($url == "")?"$model/formulario":$url;
        $this->LoadModel($model, 'md');
        $dados = $this->md->getDados();
        $this->form->NewForm($dados, $values, array(), $ajax, $url);
    }
    
    public function show($model, $item){
        $s = new showItemComponent($this);
        $s->setAppendName($this->append_name);
        $s->setShowlabel($this->showlabel);
        $this->dados = $s->loadDados($model);
        $s->show($model, $item);
    }
    
    protected function pode_exibir($model, $item){
        $s = new showItemComponent($this);
        return $s->pode_exibir($model, $item);
    }
    
    protected function conteudo_bloqueado(){
        $s = new showItemComponent($this);
        return $s->conteudo_bloqueado();
    }
    
    protected function checkIsPrivate($dados, $name){
        $s = new showItemComponent($this);
        return $s->checkIsPrivate($dados, $name);
    }
    
    public function drawLabel($arr, $name){
        $s = new showItemComponent($this);
        $s->drawLabel($arr, $name, $this->showlabel, $this->append_name);
        $this->append_name = "";
    }
    
    public function drawTitle(&$item){
        $scomp = new showComponent($this->dados, $this->gui);
        $header = $scomp->printHeader($item, $this, false);
        if($header == "") return;
        $this->gui->opendiv('item_header', '');
            echo $this->prepend_header;
            echo $header;
            echo $this->append_header;
        $this->gui->closediv();
        $this->gui->separator();
    }
    
    public function getFkeyLink($fkmodel, $fkeyarr, $nameOfInput = ""){
        $md_var = isset($fkeyarr['dstmodel'])?$fkeyarr['dstmodel']:"";
        $md_link = ($md_var != "")? $md_var: $fkmodel;
        
        $id_model = str_replace("/", "_", $fkmodel);
        $append = $this->Html->getActionLinkIfHasPermission("$md_link/formulario/ajax", "[+]");
        if($append!= ""){
            $append = $this->Html->MakeLink("#$id_model", "[+]", "lk_$id_model");
            $this->cbox->formDialog(".lk_$id_model", $fkmodel, $fkeyarr['keys'], $nameOfInput);
        }
        return $append;
    }
    
    public function drawDestaque(&$item, $title, $description, $extra = "", $separator = true){
        if($separator) echo "<hr/>";
        if(isset($item[$title]))      { 
            $item[$title] = $this->formatType($title, $this->dados, $item[$title], $item); 
            $this->gui->subtitle($item[$title]);
            unset($item[$title]);
        }
        if(isset($item[$description])){ 
            $item[$description] = $this->formatType($description, $this->dados, $item[$description], $item);
            $this->gui->infotitle($item[$description]);
            unset($item[$description]);
        }
        if(isset($item[$extra])){
            $item[$extra] = $this->formatType($extra, $this->dados, $item[$extra], $item);
            $this->gui->paragraph($item[$extra]); 
            unset($item[$extra]);
        }
        if($separator) echo "<hr/>";
    }
    
    public function getShowItemClass($name){
        return $this->show_item_class;
    }

    protected function n1info($title, $link, $chave){
        $cod  = array_keys($chave); $cod = array_shift($cod);
        $nome = array_shift($chave);
        $link = $this->Html->getLink($link."/$cod/".GetPlainName($nome));
        $this->gui->info("$title: <a href='$link'>$nome</a><br/>");
    }
        
    protected function linksnav($links){
        if(!is_array($links) || empty ($links)) return;
        
        $total = count($links);
        $i = 0;
        foreach($links as $title => $url){
            if(++$i == $total) {
                echo $title;
                return;
            }
            $link = $this->Html->getLink($url);
            echo "<a href='$link'>$title</a> > ";
        }
        
    }
    
    protected function js(){
        static $js = 0;
        if($js == 1) {return;}
        $this->Html->LoadJs(URL_JS .'/lib/component/usability');
        $js = 1;
    }
    
    protected function AddItem($link, $html, $class){
        if($html == "") return;
        $this->gui->opendiv("", $class);
        $link = $this->Html->getLink($link);
        echo "<a href='$link' style='display:block; height:100%;'>$html</a>";
        $this->gui->closediv();
    }
    
    public static function displayPathLinks($show_links, $print = true){
        if(!isset($show_links) || empty ($show_links)) return;
        $obj = new Object();
        $html = $obj->LoadResource('html', 'html');
        $v    = "";
        $var = "<ul class='breadcrumb'>";
        foreach($show_links as $label => $url){
            $var .= "<li class='breadcrumb_item'>$v";
                if($url != CURRENT_URL){
                    $var .= ($url == "")?$label:"<a href='".$html->getLink($url)."'>$label</a>";
                }else $var .= "$label";
                
            $var .= "</li>";
            //pogs...
        }
        $var .= "</ul>";
        if($print) echo $var;
        return $var;
    }
}
