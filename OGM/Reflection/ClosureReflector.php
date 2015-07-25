<?php 
namespace QNetwork\Infrastructure\OGM\Reflection;

class ClosureReflector {

	protected static $instance = null;

	protected function __construct(){

	}

	public static function getInstance(){
		
		if(static::$instance === null){
			static::$instance = new ClosureReflector();
		}

		return static::$instance;

	}

	public function getObjectPropertyByReference($object, $property){
		
		$reader = function & ($object, $property) {

			$value = & \Closure::bind(function & () use ($property) {
				
				return $this->$property;

			}, $object, $object)->__invoke();

			return $value;

		};

		$value = & $reader($object, $property);

		return $value;
	
	}

}