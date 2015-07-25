<?php 
namespace QNetwork\Infrastructure\OGM\Reflection;
use Doctrine\Common\Annotations\AnnotationReader;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use QNetwork\Infrastructure\OGM\Core\DomainObject;
use ReflectionProperty;

class NodeReflector extends Reflector{

	public static function getLabels($class){
		
		$annotation = static::getClassAnnotation($class, static::NODE_ANNOTATION);

		if($annotation === null){

			throw new OGMException('You are trying to get the labels for an object that is not a 
				node or is not defined as a node: ' . $class);

		}

		return $annotation->labels;

	}

	public static function getMatchPropertiesAsValues(DomainObject $object){

		$properties = static::getGraphProperties(get_class($object));
		$values = [];

		foreach ($properties as $property) {
			
			if( $property->match ){
				$values[$property->key] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
				settype($values[$property->key], $property->type);
			}
			
		}

		return $values;

	}

	public static function getNonMatchPropertiesAsValues(DomainObject $object){
		
		$properties = static::getGraphProperties(get_class($object));
		$values = [];

		foreach ($properties as $property) {
			
			if( ! $property->match ){
				$values[$property->key] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);
				settype($values[$property->key], $property->type);
			}
			
		}

		return $values;

	}

	public static function getOneToOnePropertiesAsValues(DomainObject $object){

		$properties = static::getPropertiesWithAnnotation(get_class($object), 'QNetwork\\Infrastructure\\OGM\\Annotations\\OneToOne');
		$values = [];

		foreach ($properties as $property) {
			
			$values[$property->type] = ClosureReflector::getInstance()->getObjectPropertyByReference($object, $property->propertyName);

		}
		
		return $values;

	}

	public static function getRepository($class){

		$annotation = static::getClassAnnotation($class, static::NODE_ANNOTATION);

		if($annotation === null){

			throw new OGMException('You are trying to get the repository for an object that is not a 
				node or is not defined as a node: ' . $class);

		}

		if( $annotation->repository ){
			
			return $annotation->repository;

		}

		if( static::hasParent($class, 'QNetwork\Infrastructure\OGM\Core\Entity') ){

			return "\QNetwork\Infrastructure\OGM\Repositories\NodeEntityRepository";

		}

		if( static::hasParent($class, 'QNetwork\Infrastructure\OGM\Core\ValueObject') ){

			return "\QNetwork\Infrastructure\OGM\Repositories\NodeValueObjectRepository";

		}		

		throw new OGMException('Unexpected behavior when requesting repository for: ' . $class);

	}

	public static function getMapper($class){

		$annotation = static::getClassAnnotation($class, static::NODE_ANNOTATION);

		if($annotation === null){

			throw new OGMException('You are trying to get the mapper for an object that is not a 
				node or is not defined as a node: ' . $class);

		}

		if( $annotation->mapper ){
			
			return $annotation->mapper;

		}

		if( static::hasParent($class, 'QNetwork\Infrastructure\OGM\Core\Entity') ){

			return "\QNetwork\Infrastructure\OGM\Mappers\NodeEntityMapper";

		}

		if( static::hasParent($class, 'QNetwork\Infrastructure\OGM\Core\ValueObject') ){

			return "\QNetwork\Infrastructure\OGM\Mappers\NodeValueObjectMapper";

		}		

		throw new OGMException('Unexpected behavior when requesting mapper for: ' . $class);

	}		

}