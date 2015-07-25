<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/**
 * A general heuristic is that value objects should be entirely immutable; check DDD;
 * If you want to change a value object you should replace the object with a new one and not be 
 * allowed to update the values of the value object itself - updatable value objects lead to 
 * aliasing problems.
 *
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
abstract class ValueObject extends DomainObject{

	public function __construct(){

		parent::__construct();
		
	}

	/**
	 * @return boolean
	 */
	public function isEntity(){

		return false;

	}
	
}