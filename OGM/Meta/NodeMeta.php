<?php 
namespace QNetwork\Infrastructure\OGM\Meta;
use QNetwork\Infrastructure\OGM\Core\DomainObject;
use QNetwork\Infrastructure\OGM\Core\AssociationType;
use QNetwork\Infrastructure\OGM\Annotations\Node;
use QNetwork\Infrastructure\InfrastructureException;
use ReflectionProperty;

/**
 * Decodes the annotations of the domain objects in order to map them to DB
 *
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class NodeMeta extends MetaMapping {

	protected $object = null;
	protected $annotation = null;

	public function __construct(DomainObject $object, Node $annotation){

		parent::__construct();
		$this->object = $object;
		$this->annotation = $annotation;

	}

	public function getObject(){

		return $this->object;

	}

	public function getAnnotation(){

		return $this->annotation;

	}

	public function getLabels(){

		return $this->annotation->labels;

	}

	public function getMapperNamespace(){

		if( $this->getAnnotation()->mapper ){
			
			return $this->getAnnotation()->mapper;

		}

		if( $this->getObject()->isEntity() ){

			return "\QNetwork\Infrastructure\OGM\Mappers\NodeEntityMapper";

		}

		if( ! $this->getObject()->isEntity() ){

			return "\QNetwork\Infrastructure\OGM\Mappers\NodeValueObjectMapper";

		}		

		throw new InfrastructureException('Unexpected behavior when requesting mapper');

	}

	public function getRepositoryNamespace(){

		if( $this->getAnnotation()->repository ){
			
			return $this->getAnnotation()->repository;

		}

		if( $this->getObject()->isEntity() ){

			return "\QNetwork\Infrastructure\OGM\Repositories\NodeEntityRepository";

		}

		if( ! $this->getObject()->isEntity() ){

			return "\QNetwork\Infrastructure\OGM\Repositories\NodeValueObjectRepository";

		}		

		throw new InfrastructureException('Unexpected behavior when requesting repository');

	}

	/**
	 * Returns all the associations for a domain object that is a node
	 *
	 * @return array(array(DomainObject, Annotation))
	 */
	public function getAssociations(){

		return array_merge($this->getOneToOneAssociations(), $this->getOneToManyAssociations());

	}

	/**
	 * @return array(array(value, OneToOne))
	 */
	public function getOneToOneAssociations(){

		return $this->getPropertiesWithAnnotation("QNetwork\Infrastructure\OGM\Annotations\OneToOne");
		
	}

	/**
	 * @return array(array(value, OneToMany))
	 */
	public function getOneToManyAssociations(){

		return $this->getPropertiesWithAnnotation("QNetwork\Infrastructure\OGM\Annotations\OneToMany");

	}

	/**
	 * Gets the object's properties that have the @GraphProperty annotation.
	 * The format used for returning this is array(array(value, GraphProperty)) where
	 * value is the exact value (object, string, int etc) that the property
	 * of the object has.
	 *
	 * @return array(array(value, GraphProperty))
	 */
 	public function getGraphProperties(){

		return $this->getPropertiesWithAnnotation("QNetwork\Infrastructure\OGM\Annotations\GraphProperty");
	}

	/**
	 * Gets the properties of the object that should be mapped 
	 * to the node and returns them in a (key, value) format
	 * that can be pushed to DB
	 *
	 * @return array(key, value)
	 */
	public function getNodeProperties(){

		return array_merge($this->getMatchProperties(), $this->getNonMatchProperties());

	}

	/**
	 * Gets only the properties of the object that are being
	 * used to do a match on DB; eg. Id for Entities, email for EmailAddress
	 *
	 * @return array(key, value) 
	 */
	public function getMatchProperties(){

		$properties = $this->getGraphProperties();

		$matchProperties = [];
		foreach ($properties as $property) {

			$value = $property[0];
			$graphProperty = $property[1];
			
			$match = $graphProperty->match;
			settype($value, ($graphProperty->type) );
			$key = $graphProperty->key;

			if( $match ){
				$matchProperties[$key] = $value;
			}

		}

		return $matchProperties;

	}

	/**
	 * The same as match properties but returning only the properties
	 * that are not part of the match process
	 *
	 * @return array(key, value)
	 */
	public function getNonMatchProperties(){

		$properties = $this->getGraphProperties();
		
		$nonMatchProperties = [];
		foreach ($properties as $property) {

			$value = $property[0];
			$graphProperty = $property[1];
			
			$match = $graphProperty->match;
			settype($value, ($graphProperty->type) );
			$key = $graphProperty->key;

			if( ! $match ){
				$nonMatchProperties[$key] = $value;
			}

		}
		
		return $nonMatchProperties;

	}

	/**
	 * Loops through all the properties an returns the ones with
	 * the $annotation
	 *
	 * @param string
	 * @return array
	 */
	private function getPropertiesWithAnnotation($annotation){

		$associations = [];

		$reflector = $this->getReflector();
		$properties = $reflector->getProperties();
		
		/**
		 * looping through all properties of the object
		 */
		foreach ($properties as $property) {
			
			$value = $this->getPropertyIfItHasAnnotation($property, $annotation);
			if($value){

				$associations[] = $value;

			}

		}

		return $associations;

	}

	/**
	 * Checks if a $property has $annotation and if so it returns it
	 *
	 * @param ReflectionProperty
	 * @param string
	 * @return array | null
	 */
	private function getPropertyIfItHasAnnotation(ReflectionProperty $property, $annotation, DomainObject $object = null){

		if($object === null){

			$object = $this->getObject();

		}

		/** 
		 * getting the value that this property is holding
	     * eg. getting the "username" (EmailAddress) for a User
	     */
		$value = $this->getObjectPropertyByReference($object, $property->getName());

		/**
		 * getting the annotation (eg. one-to-one, one-to-many) if it has it
		 */
		$class = $this->getAnnotationReader()->getPropertyAnnotation($property, $annotation);
			
		if($class && $value !== null){

			return [ $value, $class ];

		}

		return null;

	}

}