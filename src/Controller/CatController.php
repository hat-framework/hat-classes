<?php

namespace classes\Controller;
class CatController extends CController{
    
    public $model_name = "";
    public function __construct($vars) {
        parent::__construct($vars);
        if($this->model_name == ""){
            throw new \Exception("a variavel modelname não pode ser vazia");
        }
        $this->LoadModel($this->model_name, "model");
    }
}