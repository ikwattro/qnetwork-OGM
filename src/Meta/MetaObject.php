<?php 
namespace Meta;
use Proxy\ValueHolder;
use Core\OGMException;
use Reflection\Reflector;
use Reflection\ClosureReflector;
use Core\Collection;
use Core\ObjectState;

/**
 * This object has the following characteristics:
 * 1) Knows how to get the domain object embedded
 * 2) Validates the annotations present on the domain object
 * 3) Contains logic about all the properties and associations present on the object
 * 
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
class MetaObject {

	protected $isNode = null;
	protected $isEntity = null;
	protected $object = null;

	protected $proxy = null;

	public function __construct($object){

		$this->object = $object;

	}

	public function getClass(){

		return get_class($this->getDomainObject());
		
	}

	/** 
	 * Loops over an object's graph properties(GraphProperty annotation) and returns
	 * the real values that are being hold by the object
	 *
	 * @param object
	 * @return [mixed]
	 */
	public function getProperties(){

		$class = $this->getClass();
		$object = $this->getDomainObject();

		$properties = Reflector::getGraphProperties($class);
		$values = [];

		foreach ($properties as $property) {
			
			$values[$property->key] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
			settype($values[$property->key], $property->type);

		}
		
		return $values;

	}


	public static function getFromValueHolder(ValueHolder $proxy){
		
		$meta = new static();
		$meta->setProxy($proxy);

		return $meta;

	}

	public static function getFromDomainObject($object){

		$meta = new static();
		$meta->setDomainObject($object);

		return $meta;

	}

	public function getProxy(){

		return $this->proxy;

	}

	protected function setProxy(ValueHolder $proxy){

		$this->proxy = $proxy;
		return $this;

	}

	public function getDomainObject(){

		if( ! $this->object && $this->getProxy() ){
			$this->object = $this->getProxy()->__load();
			$this->validate();
		}

		return $this->object;

	}

	protected function setDomainObject($object){
		
		$this->object = $object;

		// Throw exception if object doesn't have all required annotations
		$this->validate();
		return $this;

	}

	/**
	 * to validate an object we need to check:
	 * - is it an @Entity or @Value Object, if none throw exception
	 * - if Entity is has to have the @Id annotation on one and only one of the properties
	 * - if ValueObject is has to have the @Match on one and only one of the properties
	 * - it has to be @Node or @Relationship, if no annotation present throw exception
	 * - if @Node it has to have at least one label
	 * - if @Relationship it has to have 2 properties @Start and @End
	 */
	protected function validate(){

		$object = $this->getDomainObject();

		if( ! is_object($object) ){
			throw new OGMException();
		}

		$class = get_class($object);
		$isEntity = Reflector::hasClassAnnotation($class, Reflector::ENTITY_ANNOTATION);
		$isValueObject = Reflector::hasClassAnnotation($class, Reflector::VALUE_OBJECT_ANNOTATION);

		if( ! $isEntity && ! $isValueObject ){
			throw new OGMException('The object provided does not have either the @Entity annotation, or the @ValueObject annotation.');
		}

		if( $isEntity && $isValueObject ){
			throw new OGMException('The object provided has both the @Entity and @ValueObject annotations.');
		}

		$this->isEntity = $isEntity;

		if( $this->isEntity() ){
			// $this->haltIfNoIdAnnotation();
		}

		if( ! $this->isEntity() ){
			// $this->haltIfNoMatchAnnotation();
		}

		$this->isNode = Reflector::hasClassAnnotation($class, Reflector::NODE_ANNOTATION);

		// $this->mapGraphPropertiesAnnotations();
		// $this->mapRepositoryAnnotation();

		// Reflector::getPropertiesWithAnnotation($class, 'ID_ANNOTATION');
		// Reflector::getPropertiesWithAnnotation($class, 'MATCH_ANNOTATION');

	}

	public function isEntity(){

		return $this->isEntity;

	}

	public function getAssociations(){

		$associations = [];
		$object = $this->getDomainObject();

		// TODO: make this work for relationships as well not just nodes
		$oneToOne = Reflector::getOneToOneAssociationsAsValues($object);
		$oneToMany = Reflector::getOneToManyAssociationsAsValues($object);
		
		foreach ($oneToMany as $collection) {
			
			// TODO: check if collection is actually iterable !!!! throw exception if not
			$associations[] = new Collection($collection);

		}

		foreach ($oneToOne as $object) {
			$associations[] = $object;
		}
		
		return $associations;

	}

	public function getHash(){

		/*
		 * If there is a proxy associated with this but not initilized then
		 * no hash can be returned;
		 */
		if( $this->getProxy() && ! $this->getProxy()->__isInitialized() ){
			return false;
		}

		return spl_object_hash( $this->getDomainObject() );

	}

	public function getState(){
		
		// State is NEW if the meta object doesn't have a proxy attached 
		if( ! $this->getProxy() ){
			return ObjectState::STATE_NEW;
		}

	}

	public function getRepositoryNamespace(){
		
		/*$reflector = Reflector::getReflectorClass($class);
		$mapper = $reflector::getMapper($class);
		
		return \App::make($mapper, [$this, $class]);*/

	}

	public function getMapperNamespace(){

		$mapper = Reflector::getClassAnnotation(get_class($this->getDomainObject()), Reflector::MAPPER_ANNOTATION);
		if( $mapper !== null){
			return $mapper->namespace;
		}

		return 'Mapping\NodeMapper';

	}




}