<?php 
namespace Meta;

abstract class Node extends MetaObject{

	abstract public function getRepositoryNamespace();
	abstract public function getMapperNamespace();
	
	public function getLabels(){

		$reflector = $this->getReflector();
		$annotation = $reflector->getClassAnnotation($this->getClass(), $reflector::NODE_ANNOTATION);

		return $annotation->labels;

	}

}