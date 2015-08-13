<?php
namespace Meta;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass as PHPReflectionClass; 
use ReflectionProperty as PHPReflectionProperty;

class ReflectionClass implements Reflector{

	/**
	 * The annotation reader that has been passed; this should implement caching 
	 * for better performance as reflection is an expensive operation;
	 *
	 * @var Doctrine\Common\Annotations\AnnotationReader
	 */
	protected $reader = null;

	/**
	 * The registry used for registering the annotations.
	 * 
	 * @var Doctrine\Common\Annotations\AnnotationRegistry
	 */
	protected $registry = null;

	/**
	 * Keeping a map of all reflectors that have been created;
	 * a second request for the same class namespace we just return the 
	 * reflector already present, rather than creating a new one;
	 *
	 * @var [namespace, object]
	 */
	protected $reflectors = [];

	const NODE_ANNOTATION = 'Annotations\Node';
	const RELATIONSHIP_ANNOTATION = 'Annotations\Relationship';

	const REPOSITORY_ANNOTATION = 'Annotations\Repository';
	const MAPPER_ANNOTATION = 'Annotations\Mapper';

	const ENTITY_ANNOTATION = 'Annotations\Entity';
	const VALUE_OBJECT_ANNOTATION = 'Annotations\ValueObject';

	const ID_ANNOTATION = 'Annotations\Id';
	const MATCH_ANNOTATION = 'Annotations\Match';

	const RELATE_ANNOTATION = 'Annotations\RelateTo';
	const GRAPH_PROPERTY_ANNOTATION = 'Annotations\GraphProperty';

	public function __construct(AnnotationReader $reader, AnnotationRegistry $registry){

		$this->reader = $reader;
		$this->registry = $registry;

		$this->registerAnnotations();

	}

	public function getReader(){

		return $this->reader;

	}

	public function getReflector($class){

		if( isset($reflectors[$class]) ){
			return $reflectors[$class];
		}

		$reflector = new PHPReflectionClass($class);
		$reflectors[$class] = $reflector;

		return $reflector;

	}

	public function getClassAnnotation($class, $annotation){

		return $this->getReader()->getClassAnnotation($this->getReflector($class), $annotation);

	}

	protected function registerAnnotations(){

		$path = dirname(__FILE__);
		
		$files = [
			'/../Annotations/' . 'Node.php',
			'/../Annotations/' . 'Relationship.php',

			'/../Annotations/' . 'Repository.php',
			'/../Annotations/' . 'Mapper.php',

			'/../Annotations/' . 'Entity.php',
			'/../Annotations/' . 'ValueObject.php',

			'/../Annotations/' . 'Match.php',
			'/../Annotations/' . 'Id.php',
			
			'/../Annotations/' . 'GraphProperty.php',
			'/../Annotations/' . 'RelateTo.php'		
		];
		
		$registry = $this->registry;	
		foreach ($files as $file) {
			$registry::registerFile($path . $file);
		}

	}

}