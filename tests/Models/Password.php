<?php 
namespace Models;

class Password {

	protected $password = null;

	public function __construct($password){

		$this->password = $password;

	}

	public function __toString(){

		return (string) $this->password;

	}
	
}