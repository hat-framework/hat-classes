<?php
namespace classes\Component;
class GUI{
    public function title($title){
        if($title != "") echo "<h1 class='title'>$title</h1>";
    }
    
    public function subtitle($title){
        if($title != "") echo "<h2 class='subtitle'>$title</h2>";
    }
    
    public function infotitle($title){
        if($title != "") echo "<h4 class='infotitle'>$title</h4>";
    }
    
    public function label($label, $id = "", $class=''){
        if($label != "") echo "<span class='label $class' id='$id'>$label</span>";
    }
    
    public function box($title, $content, $class = 'box'){
        $this->opendiv('', $class);
            $this->label($title, '', 'box_label');
            $this->paragraph($content, 'box_paragraph');
        $this->closediv();
    }
    
    public function paragraph($paragraph, $class = ''){
        if($paragraph != "") echo "<p class='$class'>$paragraph</p>";
    }
    
    public function info($title){
        if($title != "") echo "<div class='info'>$title</div>";
    }
    
    public function warning($title){
        if($title != "") echo "<div class='alert'>$title</div>";
    }
    
    public function html($item){
        if($item != "") echo "$item";
    }
    
    public function image($image, $class, $extra = ''){
        if($image == "") return;
        if(strstr($image, 'http:')){
            echo "<img src='$image' class='$class' $extra/>";
        }else{
            $this->Html->LoadImage($image, $class);
        }
    }
    
    public function widgetOpen($id = "", $class=""){
        $this->opendiv($id, "$class widget");
    }
    
    public function widgetClose(){
        $this->closediv();
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
    }
    
    public function closediv(){
        echo "</div>";
    }
    
    public function clear(){
        echo "<div class='clear'></div>";
    }
    
    public function separator(){
        echo "<div class='separator'></div>";
    }
    
    public function line(){
        echo "<br/>";
    }
}