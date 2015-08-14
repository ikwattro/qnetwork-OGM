<?php 
namespace Meta;
use Core\OGMException;

abstract class Node extends MetaObject{

	abstract public function getRepositoryNamespace();
	abstract public function getMapperNamespace();

	public function getLabels(){

		$reflector = $this->getReflector();
		$annotation = $reflector->getClassAnnotation($this->getClass(), $reflector::NODE_ANNOTATION);

		return $annotation->labels;

	}

	public function getAssociations($object = null){

		$reflector = $this->getReflector();
		$annotations = $reflector->getPropertiesWithAnnotation($this->getClass(), $reflector::RELATE_ANNOTATION);

		$values = [];
		// if no object is being provided then we return the annotations objects
		if($object == null){
			foreach ($annotations as $annotation) {
				$values[$annotation->propertyName] = $annotation;
			}
			return $values;
		}

		// if an object is provided, we first check if it is the same class
		// as the one stored and if so we loop through all annotations and return the values
		// hold by the object
		if( $this->getClass() !== get_class($object) ){
			throw new OGMException('Invalid object provided for getting the associations; the meta object class is different then the class that the object provided belongs to.');
		}

		foreach ($annotations as $annotation) {
			$values[$annotation->propertyName] = $reflector->getObjectPropertyValue($object, $annotation->propertyName);
		}

		return $values;

	}

}