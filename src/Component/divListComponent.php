<?php
namespace classes\Component;
class divListComponent extends listComponent{
    
    public function listar(){
        
        if(!$this->showOnlyListItem){
            echo "<div$this->class> ";
                if(!empty($this->itens)) $this->gui->title($this->title);
                echo "<ul id='$this->id' class='list'>";
        }
        
        if(is_array($this->itens) && !empty ($this->itens)){
                foreach($this->itens as $item){
                    if(!$this->pode_exibir($this->model, $item)) continue;
                    $class = $this->getEnumClass($item);
                    echo "<li class='list-item list_$this->id $class'>";
                        $this->DrawItem($item);
                    echo "</li>";
                }
        }

        if(!$this->showOnlyListItem) echo "</ul>";
        $this->print_paginator_if_exists($this->obj);
        if(!$this->showOnlyListItem) echo "</div>";     
        
        $this->itens = array();
    }
    
    protected function DrawItem($item){
        
        if(empty ($item)) return;
        $id   = implode("-", $this->getPkeyValue($item));
        echo "<div class='container' id='$id'>";
        foreach($item as $name => $it){
            if($this->checkIsPrivate($name)) {continue;}
            $it = $this->formatType($name, $it, $item);
            if(!is_array($it)){
                echo "<span class='$name'>$it</span>";
                continue;
            }
            
            if(isset($this->dados[$name]['fkey'])){
                 $this->LoadComponent($this->dados[$name]['fkey']['model'], 'md');
                 $this->md->listar($this->dados[$name]['fkey']['model'], $it);
            }
            else die(__METHOD__ . " - Erro!! ");//$this->show($this->model, $it);
        }
        
        $links = $this->getActionLinks($item);
        $link  = (is_array($links))? implode(" ", $links):"";
        echo "$link</div>";
    }
    
}

?>