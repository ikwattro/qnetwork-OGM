<?php 
namespace Core;
use Meta\MetadataFactory;
use Proxy\Proxy;

class UnitOfWork2 {

	/**
	 * Handles database connection and knows how to handle transactions.
	 *
	 * @var Core\TransactionalManager
	 */
	protected $manager = null;

	/**
     * The metadata factory, used to retrieve the OGM metadata of domain objects.
     *
     * @var Meta\MetaObject
     */
	protected $metadataFactory = null;

	public function __construct(TransactionalManager $manager, MetadataFactory $metadataFactory){

		$this->manager = $manager;
		$this->metadataFactory = $metadataFactory;

	}

	/**
     * Persists a domain object as part of the current unit of work.
     *
     * @param object $object The domain object to persist.
     * @return this
     * @throws Core\OGMException
     * @throws Core\InvalidClassException
     */
	public function persist($object){

		if( ! is_object($object) ){
			throw new OGMException('You are not allowed to persist something that is not an object.');
		}

		if( $object instanceof Collection ){
			$this->persistCollection($object);
		}

		// TODO: handle proxies
		// we have to track the proxy objects aas well; they should behave as normal objects
		// If the object provided is a proxy and is not initialized, skip
		/*if( $object instanceof Proxy && ! $object->__isInitialized() ){
			return $this;
		}*/
 
		// If the object provided does not contain the right annotation
		// this will throw an exception
		$meta = $this->getClassMetadata(get_class($object));
		$meta2 = $this->getClassMetadata(get_class($object));
		$meta3 = $this->getClassMetadata(get_class($object));
		if($meta === $meta2 && $meta2 === $meta3){
			dd('ola this shit works');
		}
		dd($this->getMapper($object));
		dd($meta->getMapperNamespace());
		dd($class);
	}

	/**
	 * Persist a collection as part of the current unit of work.
	 *
	 * @param Core\Collection
	 * @return this
	 */
	public function persistCollection($collection){

		if( ! $object instanceof Collection ){
			throw new OGMException('You are allowed to persist only collections that extend Core\Collection');
		}

		foreach ($collection as $object) {
			$this->persist($object);
		}

		return $this;

	}

	/**
	 * Gets the metadata for the provided $class (namespace)
	 * and validates against the annotations required.
	 *
	 * @param string
	 * @return Meta\MetaObject
	 * @throws Core\InvalidClassException
	 */	
	protected function getClassMetadata($class){

		return $this->metadataFactory->getMetadataFor($class);

	}

	/**
	 * Gets the repository for the provided class namespace or object.
	 *
	 * @param string | DomainObject
	 * @return Repositories\AbstractRepository
	 * @throws Core\InvalidClassException
	 */	
	public function getRepository($class){

		// if we pass an object instead of the namespace of the class
		if( is_object($class) ){
			$class = get_class($class);
		}

		$namespace = $this->getClassMetadata($class)->getRepositoryNamespace();
		return new $namespace($this);

	}

	/**
	 * Gets the mapper for the provided class namespace or object.
	 *
	 * @param string | DomainObject
	 * @return Mapping\AbstractRepository
	 * @throws Core\InvalidClassException
	 */	
	public function getMapper($class){

		// if we pass an object instead of the namespace of the class
		if( is_object($class) ){
			$class = get_class($class);
		}

		$namespace = $this->getClassMetadata($class)->getMapperNamespace();
		return new $namespace($this);

	}

}