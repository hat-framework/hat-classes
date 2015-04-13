<?php
namespace classes\Component;
class GUI extends \classes\Classes\Object{
    public $description = '';
    public $icon = '';
    
    public function title($title, $attrs = array()){
        $attr = $this->prepareAttrs($attrs);
        if($title != "") {echo "<h1 class='title' $attr>$title</h1>";}
    }
    
    public function subtitle($title, $attrs = array()){
        $attr = $this->prepareAttrs($attrs);
        if($title != "") {echo "<h2 class='subtitle' $attr>$title</h2>";}
    }
    
    public function panelSubtitle($title) {
        if ($title == ""){return $this;}
        $var = '';
        $var.= "<div class='panel-heading'>";
            $var.="<h3 class='panel-title'>";
             if ($this->icon != '')
            $var.= "<i class='fa $this->icon'></i>";
        if ($this->description != '') {
            $var.= "<div class='pull-right'>";
            $this->LoadResource('js/tooltip', 'tool');
            $this->tool->setPlacement('left');
            $var.= $this->tool->iconTool($this->description);
            $var.= "</div>";
        }
         $var.="$title</h3>";
        $var.= "</div>";
        echo $var;
        return $this;
    }

    public function infotitle($title, $attrs = array()){
        $attr = $this->prepareAttrs($attrs);
        if($title != "") {echo "<h4 class='infotitle' $attr>$title</h4>";}
        return $this;
    }
    
    public function label($label, $id = "", $class='', $attrs = array()){
        $attr = $this->prepareAttrs($attrs);
        if($label != "") {echo "<span class='label $class' id='$id' $attr>$label</span>";}
        return $this;
    }
    
    public function box($title, $content, $class = 'box', $attrs = array()){
        $this->opendiv('', $class,$attrs);
            $this->label($title, '', 'box_label');
            $this->paragraph($content, 'box_paragraph');
        $this->closediv();
        return $this;
    }
    
    public function paragraph($paragraph, $class = ''){
        if($paragraph != "") echo "<p class='$class'>$paragraph</p>";
        return $this;
    }
    
    public function info($title){
        if($title != "") echo "<div class='info'>$title</div>";
        return $this;
    }
    
    public function warning($title){
        if($title != "") echo "<div class='alert'>$title</div>";
        return $this;
    }
    
    public function html($item){
        if($item != "") {echo "$item";}
        return $this;
    }
    
    public function image($image, $class, $extra = ''){
        if($image == "") return;
        if(strstr($image, 'http:')){
            echo "<img src='$image' class='$class' $extra/>";
        }else{
            $this->Html->LoadImage($image, $class);
        }
        return $this;
    }
    
    public function widgetOpen($id = "", $class=""){
        $this->opendiv($id, "$class widget");
        return $this;
    }
    
    public function widgetClose(){
        $this->closediv();
        return $this;
    }
    
    public function opendiv($id = "", $class="", $attrs = array()){
        
        if($id != ""){
            $i  = GetPlainName($id);
            $id = "id='$i'";
        }
        
        if($class != ''){
            $cl = explode(" ", $class);
            $cls = array();
            foreach($cl as $c){
                $cls[] = GetPlainName($c);
            }
            $class = "class='".implode(" ", $cls)."'";
        }
        
        $attr = $this->prepareAttrs($attrs);
        
        echo "<div $id $class $attr>";
        return $this;
    }
    
    public function closediv(){
        echo "</div>";
        return $this;
    }
    
    public function clear(){
        echo "<div class='clear'></div>";
        return $this;
    }
    
    public function separator(){
        echo "<div class='separator'></div>";
        return $this;
    }
    
    public function line(){
        echo "<br/>";
        return $this;
    }
    
     public function setDescription($description){
        $this->description = $description;
        return $this;
    }
    
     public function setIcon($icon){
        $this->icon = $icon;
        return $this;
    }
    
    public function openPanel($class, $id = ''){
        $panel = \classes\Classes\Template::getClass("panel", "default");
        if(is_array($panel)){$panel = $panel['panel_class'];}
        $this->opendiv($id, "panel panel-$panel $class");
        return $this;
    }
    
    public function panelHeader($title, $icon = ''){
        $panel = \classes\Classes\Template::getClass("panel", "panel-heading");
        if(is_array($panel)){$panel = $panel['header'];}
        $this->opendiv('', $panel);
            $icon = ($icon !== "")?"<i class='$icon'></i>":"";
            $this->infotitle("$icon $title");
        return $this->closediv();
    }
    
    public function panelBodyOpen(){
        $panel = \classes\Classes\Template::getClass("panel", "panel-body");
        if(is_array($panel)){$panel = $panel['body'];}
        return $this->opendiv('', $panel);
    }
    
    public function panelBodyClose(){
        return $this->closediv();
    }
    
    public function panelBody($content){
        $this->panelBodyOpen();
        echo $content;
        return $this->closediv();
    }
    
    public function closePanel(){
        return $this->closediv();
    }
    
    private function prepareAttrs($attrs){
        if(empty($attrs)){return "";}
        if(!is_array($attrs)){return $attrs;}
        $attr = '';
        foreach($attrs as $name => $val){
            $attr .= ' '.$name.'="'.$val.'"';
        }
        return $attr;
    }
}