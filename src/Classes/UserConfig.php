<?php

namespace classes\Classes;
class UserConfig extends \classes\Classes\Object{
    protected $config = array();
    protected $groups = array();
    public function getUserConfigForm(){
        return $this->config;
    }
    public function getUserConfigGroup(){
        return $this->groups;
    }
}