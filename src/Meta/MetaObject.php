<?php 
namespace Meta;

abstract class MetaObject {

	protected $class = null;
	protected $reflector = null;

	protected $repository = null;
	protected $mapper = null;

	abstract protected function validate();
	abstract public function getAssociations();

	public function __construct($class, Reflector $reflector){

		$this->class = $class;
		$this->reflector = $reflector;

		$this->validate();
		
	}

	public function getClass(){

		return $this->class;

	}

	public function getReflector(){

		return $this->reflector;
		
	}

	/**
	 * Loops through all properties on this class and finds the GraphProperty annotation.
	 * If object is present then the real values that the object holds will be returned;
	 *
	 * @param DomainObject | null
	 */
	public function getProperties($object = null){

		$reflector = $this->getReflector();
		$annotations = $reflector->getPropertiesWithAnnotation($this->getClass(), $reflector::GRAPH_PROPERTY_ANNOTATION);
		
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