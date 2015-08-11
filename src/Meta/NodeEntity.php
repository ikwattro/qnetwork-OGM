<?php 
namespace Meta;
use Reflection\Reflector;

class NodeEntity extends Node{
	
	public function getMapperNamespace(){

		$mapper = Reflector::getClassAnnotation($this->getClass(), Reflector::MAPPER_ANNOTATION);
		if( $mapper !== null){
			return $mapper->namespace;
		}

		return 'Mapping\NodeEntityMapper';

	}

}