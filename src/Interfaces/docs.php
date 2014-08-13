<?php

namespace classes\Interfaces;
use classes\Classes\Object;
abstract class docs extends Object{
    //&lt;?php //do something  ?&gt;
    protected $flush = "";
    public function __contruct(){}    
    abstract function getDocumentacao();
    abstract function getComoUsar();
    abstract function getComoExtender();
    abstract function getExemplo();
    abstract function getMetodos();
    
    protected function Paragraph($dados){
        $this->addtoflush("<p>$dados</p>");
    }
    
    protected function Title($dados){
        $this->addtoflush("<h2>$dados</h2>");
    }
    
    protected function phpCode($dados){
        $this->Add("<code><br/> &lt;?php <br/><br/> $dados <br/><br/> ?&gt; <br/><br/></code>");
    }
    
    private function Add($dados){
        $this->addtoflush(nl2br("$dados"));
    }
    
    private function addtoflush($dados){
        $this->flush .= "$dados";
    }
    
    protected function flush(){
        $temp = $this->flush;
        $this->flush = "";
        return $temp;
    }
}
?>