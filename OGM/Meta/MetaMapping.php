<?php 
namespace QNetwork\Infrastructure\OGM\Meta;
use QNetwork\Infrastructure\OGM\Core\DomainObject;
use Doctrine\Common\Annotations\AnnotationReader as AnnotationReader;
use QNetwork\Infrastructure\OGM\Annotations\Node;
use ReflectionClass;

/** 
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
abstract class MetaMapping {

	protected $reader = null;
	protected $reflector = null;

	public function __construct(){

		$this->reader = new AnnotationReader();

	}

	abstract public function getAssociations();
	abstract public function getObject();
	abstract public function getMapperNamespace();
	abstract public function getRepositoryNamespace();

	public static function getMetaClass(DomainObject $object){
		
		$reader = new AnnotationReader();

		$reflector = new \ReflectionClass($object);
		$nodeAnnotation = $reader->getClassAnnotation($reflector, "QNetwork\Infrastructure\OGM\Annotations\Node");
		if( $nodeAnnotation instanceof Node){

			return new NodeMeta($object, $nodeAnnotation);

		}
		
		echo 'something weird went down in QNetwork\Infrastructure\OGM\Meta\MetaMapping';
		die();

	}

	public function getAnnotationReader(){

		return $this->reader;

	}

	public function getReflector(DomainObject $object = null){

		if($object === null){
			return new \ReflectionClass($this->getObject());
		}

		return new \ReflectionClass($object);

	}

	public function getObjectPropertyByReference($object, $property){
		
		$reader = function & ($object, $property) {

			$value = & \Closure::bind(function & () use ($property) {
				
				return $this->$property;

			}, $object, $object)->__invoke();

			return $value;

		};

		$value = & $reader($object, $property);

		return $value;
	
	}

}