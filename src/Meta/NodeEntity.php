<?php 
namespace Meta;
use Reflection\Reflector;

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

		return 'Repositories\NodeEntityRepository';

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

}