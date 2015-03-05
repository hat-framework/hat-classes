<?php
namespace classes\Component;
class GUI extends \classes\Classes\Object{
    public $description = '';
    public $icon = '';
    
    public function title($title){
        if($title != "") {echo "<h1 class='title'>$title</h1>";}
    }
    
    public function subtitle($title){
        if($title != "") {echo "<h2 class='subtitle'>$title</h2>";}
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

    public function infotitle($title){
        if($title != "") {echo "<h4 class='infotitle'>$title</h4>";}
        return $this;
    }
    
    public function label($label, $id = "", $class=''){
        if($label != "") {echo "<span class='label $class' id='$id'>$label</span>";}
        return $this;
    }
    
    public function box($title, $content, $class = 'box'){
        $this->opendiv('', $class);
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
    
    public function opendiv($id = "", $class=""){
        
        if($id != ""){
            $id = GetPlainName($id);
            $id = "id='$id'";
        }
        
        if($class != ''){
            $cl = explode(" ", $class);
            $cls = array();
            foreach($cl as $c){
                $cls[] = GetPlainName($c);
            }
            $class = "class='".implode(" ", $cls)."'";
        }
        
        echo "<div $id $class>";
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
        $this->opendiv($id, "panel panel-info $class");
        return $this;
    }
    
    public function panelHeader($title, $icon = ''){
        $this->opendiv('', "panel-heading");
            $icon = ($icon !== "")?"<i class='$icon'></i>":"";
            $this->infotitle("$icon $title");
        return $this->closediv();
    }
    
    public function panelBody($content){
        $this->opendiv('', "panel-body");
        echo $content;
        return $this->closediv();
    }
    
    public function closePanel(){
        return $this->closediv();
    }
}