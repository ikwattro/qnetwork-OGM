<?php 
namespace Meta;
use Core\OGMException;
use ProxyManager\Proxy\LazyLoadingInterface as Proxy;

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

		if( $object instanceof Proxy ){
			$object = $object->getWrappedValueHolderValue();			
		}

		foreach ($annotations as $annotation) {
			$value = $reflector->getObjectPropertyValue($object, $annotation->propertyName);
			$values[$annotation->propertyName] = $value;
		}

		return $values;

	}

}