<?php
namespace classes\Events;
abstract class EventRegister extends \classes\Classes\Object{
    abstract public function getSendEvents();
    abstract public function getListenEvents();
    public final function register(){
        if(false === $this->registerSendEvents()){return false;}
        if(false === $this->registerListenEvents()){return false;}
        return true;
    }
    
    private function registerSendEvents(){
        $Events = $this->getSendEvents();
    }
    
    private function registerListenEvents(){
        $Events = $this->getListenEvents();
    }
}