<?php 
namespace Meta;
use Reflection\Reflector;

class MetaFactory{

	public function getMetaClassFor($object){

		$meta = null;
		$class = get_class($object);

		// if it has @Node and @Entity annotation then create a NodeEntity meta object
		if( Reflector::hasClassAnnotation($class, Reflector::NODE_ANNOTATION) &&
			Reflector::hasClassAnnotation($class, Reflector::ENTITY_ANNOTATION) ){

			$meta = new NodeEntity($object);

		}
		
		// if it has @Node and @ValueObject annotation then create a NodeValueObject meta object
		if( Reflector::hasClassAnnotation($class, Reflector::NODE_ANNOTATION) &&
			Reflector::hasClassAnnotation($class, Reflector::VALUE_OBJECT_ANNOTATION) ){

			$meta = new NodeValueObject($object);

		}

		// if it has @Relationship annotation then create a Relationship meta object
		if( Reflector::hasClassAnnotation($class, Reflector::RELATIONSHIP_ANNOTATION) ){

			$meta = new Relationship($object);

		}

		if($meta === null){
			throw new OGMException('The object provided for getting the meta class does not have the right annotations.');
		}
		
		return $meta;

	}
	
}