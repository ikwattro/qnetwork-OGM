<?php 
namespace QNetwork\Infrastructure\OGM\Reflection;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use ReflectionClass;
use ReflectionProperty;
use QNetwork\Infrastructure\OGM\Core\DomainObject;

class Reflector {
	
	const NODE_ANNOTATION = 'QNetwork\Infrastructure\OGM\Annotations\Node';
	const RELATIONSHIP_ANNOTATION = 'QNetwork\Infrastructure\OGM\Annotations\Relationship';

	public static function getReflector($class){
		
		return new ReflectionClass($class);

	}

	public static function getAnnotationReader(){

		return new AnnotationReader();

	}

	/**
	 * Gets the specific reflector class of a specific domain object (NodeReflector, RelationshipReflector).
	 * The class will be returned in the form of a full namespace
	 * and it is the client's responsability to instantiate it.
	 * 
	 * @param string full namespace of the class
	 * @param string full namespace of the meta class
	 * @return string full node | relationship namespace
	 * @throws OGMException if class has no annotation
	 */
	public static function getReflectorClass($class){

		$isNode = static::hasClassAnnotation($class, static::NODE_ANNOTATION);
		if($isNode){
			return '\\QNetwork\\Infrastructure\\OGM\\Reflection\\NodeReflector';
		}

		$isRelationship = static::hasClassAnnotation($class, static::RELATIONSHIP_ANNOTATION);
		if($isRelationship){
			return '\\QNetwork\\Infrastructure\\OGM\\Reflection\\RelationshipReflector';
		}

		throw new OGMException('The class provided for meta '. $class .' has no annotation defined.');

	}

	/**
	 * Gets all annotations for a specific class. 
	 * The annotations will be returned as instances of the annotation classes
	 *
	 * @param string full namespace of the class
	 * @return array(OGMAnnotation)
	 */
	public static function getClassAnnotations($class){

		$reader = static::getAnnotationReader();
		$reflector = static::getReflector($class);
		
		return $reader->getClassAnnotations($reflector);
		
	}

	/**
	 * Gets the specific for a class given the annotation name.
	 * Used for getting labels, repositories or mappers
	 *
	 * @param string full class namespace
	 * @param string full annotation namespace
	 * @return OGMAnnotation | null returns the specific annotation or null if class doesn't have $annotation
	 */
	public static function getClassAnnotation($class, $annotation){

		$reader = static::getAnnotationReader();
		$reflector = static::getReflector($class);

		return $reader->getClassAnnotation($reflector, $annotation);

	}

	/**
	 * Checks whether $class - full namespace - has an $annotation
	 * at the class level. 
	 *
	 * @param string full namespace of the class
	 * @param string full namespace of the annotation
	 * @return boolean 
	 */
	public static function hasClassAnnotation($class, $annotation){

		$reader = static::getAnnotationReader();
		$reflector = static::getReflector($class);

		$check = $reader->getClassAnnotation($reflector, $annotation);
		if($check instanceof $annotation){
			
			return true;

		}

		return false;

	}

	/**
	 * Loops over all parents of class and checks if it has parent
	 *
	 * @param string full namespace of the class that is being checked
	 * @param string full namespace of the parent class that is targeted
	 */
	public static function hasParent($class, $parentToSearch, $includingRoot = true){

		/**
		 * If the search includes the $includingRoot true then we consider root a parent
		 */
		if($includingRoot && $class === $parentToSearch){
			return true;
		}

		$parents = class_parents($class);
		foreach ($parents as $parent) {
			
			if($parent === $parentToSearch){
				return true;
			}

		}

		return false;

	}

	/**
	 * Checks whether a class is an entity or a value object 
	 * @param string | object
	 * @return boolean
	 * @throws OGMException
	 */
	public static function isEntity($class){

		if(static::hasParent($class, 'QNetwork\\Infrastructure\\OGM\\Core\\Entity')){
			return true;
		}

		if(static::hasParent($class, 'QNetwork\\Infrastructure\\OGM\\Core\\ValueObject')){
			return false;
		}

		throw new OGMException('You are trying to check if ' . $class . ' is an entity or a value object; it is neither.');
	}

	/**
	 * Loops through the class meta data and identities which
	 * properties have GraphProperty attached to them.
	 *
	 * @param string full class namespace for which the look-up is taking place
	 * @return [GraphProperties]
	 */
	public static function getGraphProperties($class){
		
		return static::getPropertiesWithAnnotation($class, 'QNetwork\\Infrastructure\\OGM\\Annotations\\GraphProperty');

	}

	/** 
	 * Loops over an object's graph properties and returns
	 * the real values that are being hold by the object
	 *
	 * @param object
	 * @return [mixed]
	 */
	public static function getGraphPropertiesAsValues(DomainObject $object){

		$properties = static::getGraphProperties(get_class($object));
		$values = [];

		foreach ($properties as $property) {
			
			$values[$property->key] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
			settype($values[$property->key], $property->type);

		}
		
		return $values;

	}

	// public static function getEagerProperties(){}

	/** 
	 * Loops over an object's properties with annotation OneToOne and returns
	 * the real values that are being hold by the object.
	 *
	 * @param object
	 * @return [mixed]
	 */
	public static function getOneToOneAssociationsAsValues(DomainObject $object){
		
		$properties = static::getPropertiesWithAnnotation(get_class($object), 'QNetwork\\Infrastructure\\OGM\\Annotations\\OneToOne');
		$values = [];

		foreach ($properties as $property) {
			
			$value = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
			if( $value !== null){
				$values[$property->type] = $value;
			}

		}
		
		return $values;

	}

	public static function getOneToManyAssociationsAsValues(DomainObject $object){
		
		$properties = static::getPropertiesWithAnnotation(get_class($object), 'QNetwork\\Infrastructure\\OGM\\Annotations\\OneToMany');
		$values = [];

		foreach ($properties as $property) {
			
			$value = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
			if( $value !== null){
				$values[$property->type] = $value;
			}

		}
		
		return $values;

	}

	/**
	 * Loops through all the properties an returns the ones with
	 * the $annotation
	 *
	 * @param string
	 * @return array(OGMAnnotation)
	 */
	public static function getPropertiesWithAnnotation($class, $annotation){
		
		$reflector = static::getReflector($class);
		$properties = $reflector->getProperties();

		$annotationProperties = [];
		foreach ($properties as $property) {
			
			/**
		 	 * getting the annotation (eg. OneToOne, OneToMany, GraphProperty) if it has it
		 	 */
			$annotationProperty = static::getPropertyIfItHasAnnotation($class, $property, $annotation);
			
			if($annotationProperty){
				$annotationProperty->propertyName = $property->name;
				$annotationProperties[] = $annotationProperty;
			}
		}

		return $annotationProperties;

	}

	/**
	 * Checks if a $property has $annotation and if so it returns it
	 *
	 * @param ReflectionProperty
	 * @param string
	 * @return array | null
	 */
	public static function getPropertyIfItHasAnnotation($class, ReflectionProperty $property, $annotation){

		$reader = static::getAnnotationReader();

		/**
		 * Getting the $annotation class assigned to this $property
		 */
		$class = $reader->getPropertyAnnotation($property, $annotation);
		
		if($class){
			return $class;
		}

		return null;

	}

	/**
	 * Instantiate any class without using the constructor.
	 *
	 * @param string full class namespace
	 * @return object
	 */
	public static function newInstanceWithoutConstructor($class){

		$reflector = static::getReflector($class);
        return $reflector->newInstanceWithoutConstructor();

	}

	/**
	 * Sets property for a specific object.
	 *
	 * @param object 
	 * @param string property that should be changed
	 * @param string the value that the property should take
	 * @return void
	 */
	public static function setPropertyValueForObject($object, $property, $value){

		$reflector = static::getReflector($object);
        
        $property = $reflector->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);

	}

	public static function registerAnnotations(){

		$appPath = app_path();
		
		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'Node.php';
		AnnotationRegistry::registerFile($path);

		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'Relationship.php';
		AnnotationRegistry::registerFile($path);
		
		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'GraphProperty.php';
		AnnotationRegistry::registerFile($path);

		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'MatchProperty.php';
		AnnotationRegistry::registerFile($path);

		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'OneToOne.php';
		AnnotationRegistry::registerFile($path);

		$path = $appPath . '/Infrastructure/OGM/Annotations/' . 'OneToMany.php';
		AnnotationRegistry::registerFile($path);

	}

}