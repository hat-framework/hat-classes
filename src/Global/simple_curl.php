<?php

function simple_curl_error($e = ''){
    static $error = "";
    if($e !== ''){
        $error = $e;
        return;
    }
    return $error;
}

function simple_curl($url,$post=array(),$get=array(),$http=array(), $buildQuery = true, $timeout = 0){
        if(strstr($url, URL) !== false) {
            if(!defined('Crypty_base64key')) require_once '../../init.php';
            $post['Crypty_base64key'] = Crypty_base64key;
        }
	$url = explode('?',$url,2);
	if(count($url)===2){
            $temp_get = array();
            parse_str($url[1],$temp_get);
            $get = array_merge($get,$temp_get);
	}
	$ch = curl_init($url[0]."?".http_build_query($get));
        if($timeout > 0) {curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);}
        if(!empty($post)){
            $post = ($buildQuery)?http_build_query($post):$post;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if(!empty($http)) {curl_setopt($ch, CURLOPT_HTTPHEADER, $http);}
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$var = curl_exec($ch);
        if($var === false){
            simple_curl_error(curl_error($ch));
        }
        curl_close($ch);
        return $var;
}

/*
class curlHat{
    private $post = array();
    private $build = true;
    public function setPost($post, $buildQuery = true){
        $this->post  = $post;
        $this->build = $buildQuery;
        return $this;
    }
    
    private $get = array();
    public function setGet($get){
        $this->get = $get;
        return $this;
    }
    
    private $http = array();
    public function setHeader($http){
        $this->http = $http;
        return $this;
    }
    
    private $timeout= array();
    public function setTimeout($timeout){
        $this->timeout = $timeout;
        return $this;
    }
    
    private function prepare($url){
        if(strstr($url, URL) !== false) {
            if(!defined('Crypty_base64key')) require_once '../../init.php';
            $this->post['Crypty_base64key'] = Crypty_base64key;
        }
        
        $url = explode('?',$url,2);
	if(count($url)===2){
            $temp_get = array();
            parse_str($url[1],$temp_get);
            $this->get = array_merge($this->get,$temp_get);
	}
    }
    
    public function curl($url){
        $this->prepare($url);
        $this->post = ($this->buildQuery)?http_build_query($this->post):$this->post;
	$ch = curl_init($url[0]."?".http_build_query($this->get));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http);
	$var = curl_exec ($ch);
        curl_close($ch);
        return $var;
    }
}
*/
