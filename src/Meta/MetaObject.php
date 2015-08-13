<?php 
namespace Meta;

abstract class MetaObject {

	protected $class = null;
	protected $reflector = null;

	protected $repository = null;
	protected $mapper = null;

	abstract protected function validate();

	public function __construct($class, Reflector $reflector){

		$this->class = $class;
		$this->reflector = $reflector;

		$this->validate();
		
	}

	public function getClass(){

		return $this->class;

	}

	public function getReflector(){

		return $this->reflector;
		
	}

}