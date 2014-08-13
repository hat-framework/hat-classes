<?php
namespace classes\Classes;
class nodeElement {
	public
		$data,
		$prev=false,
		$next=false;

	public function __construct($data) {
		$this->data=$data;
	}

	public function add($data) {
		if ($this->data<$data) {
			if ($this->next) {
				$this->next->add($data);
			} else {
				$this->next=new nodeElement($data);
			}
		} else {
			if ($this->prev) {
				$this->prev->add($data);
			} else {
				$this->prev=new nodeElement($data);
			}
		}
	}

	public function show() {
		if ($this->prev) $this->prev->show();
		echo $this->data,'<br />';
		if ($this->next) $this->next->show();
	}
}

class nodeTree {
	public
		$first=false;

	public function add($data) {
		if ($this->first) {
			$this->first->add($data);
		} else {
			$this->first=new nodeElement($data);
		}
	}

	public function show() {
		if ($this->first) {
			$this->first->show();
		}
	}

}

?>