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
        $plugin   = explode("/",$plugin);
        $resource = array_shift($plugin);
        $modulo   = array_shift($plugin);
        $path     = implode("/", $plugin);
        $path     = ($path == "")? "": "/$path";
        
        $relativeDir                   = Registered::getResourceLocation($resource)."/";
        $link                          = "src/jsplugins/$modulo$path";
        $this->url                     = URL       . $relativeDir.$link ;
        $this->url_relative            = PROJECT   . $relativeDir.$link;
        $this->path                    = DIR_BASIC . $relativeDir.$link;  
        $this->resource_path           = RESOURCES . $relativeDir;        
        $this->resource_url            = URL       . $relativeDir;
        $this->resource_url_relative   = PROJECT   . $relativeDir;
        getTrueDir($this->path);
        getTrueDir($this->resource_path);
    }
    
    protected final function start_scripts($scripts) {
        if(!$scripts)return; 
        $this->Html->LoadJQuery();
        $this->init();
    }
        
    abstract function init();
}