<?php 
namespace Meta;
use ProxyManager\Proxy\LazyLoadingInterface as Proxy;

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

		if( $object instanceof Proxy ){
			$object = $object->getWrappedValueHolderValue();			
		}

		foreach ($annotations as $annotation) {
			$values[$annotation->propertyName] = $reflector->getObjectPropertyValue($object, $annotation->propertyName);
		}

		return $values;

	}

	/**
	 * Instantiate any class without using the constructor.
	 *
	 * @return object
	 */
	public function newInstanceWithoutConstructor(){

        return $this->getReflector()->newInstanceWithoutConstructor( $this->getClass() );

	}

}