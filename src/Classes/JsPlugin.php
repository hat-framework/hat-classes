<?php

namespace classes\Classes;
abstract class JsPlugin extends Object{
    
    protected $url;
    protected $url_relative;
    protected $resource_path;
    protected $resource_url;
    protected $resource_url_relative;
    protected $path;
    protected $Html;
    
    protected final function __construct($plugin) {
        $this->LoadResource("html", "Html");
        $plugin  = explode("/",$plugin);
        $resource = array_shift($plugin);
        $modulo = array_shift($plugin);
        $path   = implode("/", $plugin);
        $path   = ($path == "")? "": "/$path";
        
        $link                          = "$resource/src/jsplugins/$modulo$path";
        $this->url                     = URL       . "recursos/" .$link ;
        $this->url_relative            = PROJECT   . DIR_RESOURCE_RELATIVE ."$link";
        $this->path                    = RESOURCES . "$link";
        $this->resource_path           = RESOURCES . $resource;
        $this->resource_url            = URL       . DIR_RESOURCE_RELATIVE . "$resource";
        $this->resource_url_relative   = PROJECT   . DIR_RESOURCE_RELATIVE ."$resource";
    }
    
    protected final function start_scripts($scripts) {
        if(!$scripts)return; 
        $this->Html->LoadJQuery();
        $this->init();
    }
        
    abstract function init();
}