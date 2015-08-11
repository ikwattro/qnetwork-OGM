<?php 
namespace Meta;
use Reflection\Reflector;

class Node extends MetaObject{

	public function getLabels(){

		$class = $this->getClass();
		$annotation = Reflector::getClassAnnotation($class, Reflector::NODE_ANNOTATION);

		return $annotation->labels;

	}

}