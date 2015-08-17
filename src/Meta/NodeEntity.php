<?php 
namespace Meta;
use Core\OGMException;
use Core\Uuid;

class NodeEntity extends Node{
		
	// TODO: validate against required annotations
	protected function validate(){}

	public function getRepositoryNamespace(){

		if( isset($this->repository) ){
			return $this->repository;
		}

		$reflector = $this->getReflector();
		$repository = $reflector->getClassAnnotation($this->getClass(), $reflector::REPOSITORY_ANNOTATION);

		if( $repository !== null){
			return $repository->namespace;
		}

		return 'Repositories\NodeRepository';

	}

	public function getMapperNamespace(){

		if( isset($this->mapper) ){
			return $this->mapper;
		}

		$reflector = $this->getReflector();
		$mapper = $reflector->getClassAnnotation($this->getClass(), $reflector::MAPPER_ANNOTATION);

		if( $mapper !== null){
			return $mapper->namespace;
		}

		return 'Mapping\NodeEntityMapper';

	}

	public function setId($object, $uuid = null){
		
		$id = $this->getId($object);
		if($id === null){
			$id = new Uuid();
		}
		
		if($uuid !== null){
			$id = $uuid;
		}
		
		$annotation = $this->getId();
		$this->getReflector()->setPropertyValueForObject($object, $annotation->propertyName, $id);

		return $id;
		
	}

	public function getId($object = null){
			
		$reflector = $this->getReflector();
		$annotation = $reflector->getPropertiesWithAnnotation($this->getClass(), $reflector::ID_ANNOTATION);
		
		if(count($annotation) > 1){
			throw new OGMException('You cannot have the @Id annotation on more than one property.');
		}

		if(count($annotation) < 1){
			throw new OGMException('You cannot have an entity without defining an id. Any class that has @Entity at the level class has to have an @Id annotation at the property level in its class or any of its parents(protected scope).');
		}

		$annotation = $annotation[0];
		// if no object is being provided then we return the annotation object
		if($object == null){
			return $annotation;
		}

		// if an object is provided, we first check if it is the same class
		// as the one stored and if so we loop through all annotations and return the values
		// hold by the object
		if( $this->getClass() !== get_class($object) ){
			throw new OGMException('Invalid object provided for getting the Id; the meta object class is different then the class that the object provided belongs to.');
		}
		
		return $reflector->getObjectPropertyValue($object, $annotation->propertyName);

	}

}