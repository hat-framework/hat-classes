<?php

namespace classes\Interfaces;
class Description{
   
   protected $description = "";
   protected $label = "";
   public final function getLabel(){
       return $this->label;
   }
   
   public final function getDescription(){
       return $this->description;
   }
}
?>
