<?php 
namespace Core;
use Meta\MetadataFactory;
use Meta\NodeValueObject as MetaNodeValueObject;
use Meta\NodeEntity as MetaNodeEntity;
use Mapping\NodeFinder;

class UnitOfWork {

	/**
     * The identity map that holds references to all managed domain objects that have
     * an identity (Entities); check Domain-Driven-Design to learn more about entities and value objects; 
     * if an object that already exists in-memory is being pulled from db again
     * then a reference to the in-memory object will be returned.
     * http://martinfowler.com/eaaCatalog/identityMap.html
     *
     * @var Core\IdentityMap
     */
	protected $identityMap = null;

	/**
	 * Keeps track of all objects managed by this unit of work; the persist
	 * method has been used on them; cascade persist will be applied at commit time
	 *
	 * @var Core\Collection
	 */
	protected $managed = null;

	/**
	 * Keeps track of all objects scheduled to be deleted by this unit of work; 
	 * No cascade delete is applied;
	 *
	 * @var Core\Collection
	 */
	protected $removed = null;

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

		$this->identityMap = new IdentityMap();
		$this->managed = new Collection();
		$this->removed = new Collection();

	}

	public function getManager(){

		return $this->manager;

	}

	public function commit(){

		$allManaged = new Collection();
		foreach ($this->managed as $hash => $object) {
			$this->cascadeManage($object, $allManaged);
		}

		// update the managed objects to allManaged
		$this->managed = $allManaged;

		foreach ($this->managed as $object) {
			
			$state = $this->getDomainObjectState($object);
			switch ($state) {
				case ObjectState::STATE_NEW:
					$this->getMapper($object)->insert($object);
					break;
				
				case ObjectState::STATE_DIRTY:
					$this->getMapper($object)->update($object);
					break;

				case ObjectState::STATE_CLEAN:
					// DO nothing
					break;

				case ObjectState::STATE_REMOVED:
					$this->getMapper($object)->delete($object);
					break;

				default:
					throw new OGMException('Unexpected state for the object provided.');
					break;
			}

		}

		$this->getManager()->flush();

	}

	private function cascadeManage($object, Collection $managed){
		
		if( ! is_object($object) || $object instanceof \Traversable){
			throw new OGMException('You cannot cascade manage something that is not an object, or a traversable object.');
		}

		$this->generateIdForEntity($object);

		$hash = $this->getHash($object);
		$managed->set($hash, $object);

		$meta = $this->getClassMetadata($object);
		$associations = $meta->getAssociations($object);

		foreach ($associations as $association) {
			
			if( is_array($association) || $association instanceof \Traversable ){
				
				foreach ($association as $value) {
					$this->cascadeManage($value, $managed);
				}
				continue;

			}

			$this->cascadeManage($association, $managed);

		}

	}

	private function generateIdForEntity($object){

		$meta = $this->getClassMetadata($object);
		if($meta instanceof MetaNodeEntity){
			$meta->setId($object);
		}

		return $this;

	}

	/**
     * Persists a domain object as part of the current unit of work.
     * Traversal (cascade persist) of the object will happen at commit time.
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

		$meta = $this->getClassMetadata(get_class($object));
		$hash = $this->getHash($object);
		$this->managed->set($hash, $object);

		return $this;

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
	 * Finds the state of a given domain object; CLEAN, NEW, DIRTY, REMOVED
	 * 
	 * @param DomainObject
	 * @return ObjectState
	 */
	public function getDomainObjectState($object){

		$meta = $this->getClassMetadata($object);
			
		// Value Objects are always NEW - they will always be merged
		if($meta instanceof MetaNodeValueObject){
			return ObjectState::STATE_NEW;
		}

		// NEW - if not proxy -> not pulled from DB which means the client created the object
		return ObjectState::STATE_NEW;

		// DIRTY - if proxy -> update properties + merge only relationships with value objects

		// REMOVED - if present in the removed collection

	}

	/**
     * @param DomainObject $object
     * @return string
     */
	private function getHash($object){

		return spl_object_hash($object);

	}

	/**
	 * Gets the metadata for the provided $class (namespace)
	 * and validates against the annotations required.
	 *
	 * @param string
	 * @return Meta\MetaObject
	 * @throws Core\InvalidClassException
	 */	
	public function getClassMetadata($class){

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
		return new $namespace($this, $class);

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

	public function getNodeFinder(){

		return new NodeFinder($this);

	}

}