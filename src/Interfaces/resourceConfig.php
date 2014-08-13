<?php


namespace classes\Interfaces;
use classes\Classes\Object;
class resourceConfig extends Object{

	protected $default_class = "";
	public function getDefaultClass(){
		return $this->default_class;
	}

}

?>