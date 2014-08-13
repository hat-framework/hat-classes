<?php
namespace classes\Component;
use classes\Classes\Object;
class showComponent extends Object{
    
    private $founded = array();
    private $tags = array('title', 'subtitle', 'desc', 'label');
    public function __construct($dados, $gui) {
        $this->dados = $dados;
        foreach($this->tags as $tag){
            $this->founded[$tag] = array();
        }
        $this->findTags();
        $this->gui = $gui;
    }
    
    public function printHeader(&$item, $component = null, $print = true){
        ob_start();
        foreach($this->founded as $method => $foundeds){
            foreach($foundeds as $founded){
                if(isset($item[$founded])){
                    if($method == 'desc') $method = 'infotitle';
                    $this->gui->$method($item[$founded], GetPlainName($item[$founded]));
                    unset($item[$founded]);
                }
            }
        }
        if(is_object($component) && method_exists($component, 'afterHeader')){
            $component->afterHeader($item, $this->dados);
        }
        $contents = ob_get_contents();
        ob_end_clean();
        
        if($contents == "") return;
        if($print) echo $contents.$this->str;
        return $contents.$this->str;
        //$this->gui->separator();
    }
    
    private $str = '';
    public function setContent($str){
        $this->str = $str;
    }
    
    
    private function findTags(){
        foreach($this->dados as $name => $var){
            foreach($this->tags as $tag){
                if(!array_key_exists($tag, $var)) continue;
                $this->founded[$tag][$name] = $name;
            }
        }
    }
}