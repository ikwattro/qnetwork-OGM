<?php 
namespace Meta;
use Core\InvalidClassException;
use Proxy\Proxy;

class MetadataFactory {

	protected $reflector = null;

	/**
	 * A map for classes and metadata, if the same class requests the metadata twice
	 * an instance of the already created meta class will be returned
	 *
	 * @var [namespace, MetaObject]
	 */
	protected $metas = [];

	public function __construct(Reflector $reflector){
		
		$this->reflector = $reflector;

	}
	
	/**
	 * Gets the metadata class for the provided $class (namespace) and validates against the annotations required.
	 *
	 * @param string
	 * @return Meta\MetaObject
	 * @throws Core\InvalidClassException
	 */
	public function getMetadataFor($class){

		if( is_object($class) ){
			$class = get_class($class);
		}
		
		if( isset($this->metas[$class]) ){
			return $this->metas[$class];
		}

		$meta = null;
		
		$reflector = $this->reflector;

		// if it has @Node and @Entity annotation then create a NodeEntity meta object
		if( $this->reflector->getClassAnnotation($class, $reflector::NODE_ANNOTATION) &&
			$this->reflector->getClassAnnotation($class, $reflector::ENTITY_ANNOTATION)){

			$meta = new NodeEntity($class, $reflector);

		}
		
		// if it has @Node and @ValueObject annotation then create a NodeValueObject meta object
		if( $this->reflector->getClassAnnotation($class, $reflector::NODE_ANNOTATION) &&
			$this->reflector->getClassAnnotation($class, $reflector::VALUE_OBJECT_ANNOTATION)){
			
			$meta = new NodeValueObject($class, $reflector);
		
		}

		// if it has @Relationship annotation then create a Relationship meta object
		// Currently we consider all relationships as value objects - defined only by their properties
		if( $this->reflector->getClassAnnotation($class, $reflector::RELATIONSHIP_ANNOTATION) ){

			$meta = new Relationship($class, $reflector);

		}
		
		if($meta === null){
			throw new InvalidClassException('The object provided for getting the meta class does not have the right annotations at the class level.');
		}

		$this->metas[$class] = $meta;
		return $meta;
		
	}
}