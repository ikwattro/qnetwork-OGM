<?php 
namespace Meta;

class NodeValueObject extends Node{
	
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

		return 'Mapping\NodeValueObjectMapper';

	}


	/**
	 * Loops through all properties on this class and finds the GraphProperties with match=true annotation.
	 * If object is present then the real values that the object holds will be returned;
	 *
	 * @param DomainObject | null
	 */
	public function getMatchProperties($object = null){

		$annotations = $this->getProperties();
		$matchAnnotations = [];
		foreach ($annotations as $value) {
			if($value->match){
				$matchAnnotations[$value->propertyName] = $value;
			}
		}

		if($object === null){
			return $matchAnnotations;
		}
		
		$properties = $this->getProperties($object);
		$values = [];
		foreach ($matchAnnotations as $propertyName => $value) {
			$values[$propertyName] = $properties[$propertyName];
		}

		return $values;

	}

}