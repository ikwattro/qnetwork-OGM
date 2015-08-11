<?php 
namespace Meta;
use Reflection\Reflector;
use Reflection\ClosureReflector;

class NodeValueObject extends Node{
	
	public function getMapperNamespace(){

		$mapper = Reflector::getClassAnnotation($this->getClass(), Reflector::MAPPER_ANNOTATION);
		if( $mapper !== null){
			return $mapper->namespace;
		}

		return 'Mapping\NodeValueObjectMapper';

	}

	public function getMatchProperties(){

		$class = $this->getClass();
		$object = $this->getDomainObject();
		
		$properties = Reflector::getPropertiesWithAnnotation($class, Reflector::GRAPH_PROPERTY_ANNOTATION);
		$values = [];

		foreach ($properties as $property) {
			
			if( $property->match ){
				$values[$property->key] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
				settype($values[$property->key], $property->type);
			}
			
		}
		
		return $values;

	}

}