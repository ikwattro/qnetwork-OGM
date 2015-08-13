<?php 
namespace Core;
use Meta\MetaFactory;
use Meta\MetaObject;
use Proxy\ValueHolder;

class UnitOfWork {

	protected $identityMap = null;
	protected $managedObjects = null;
	protected $removedObjects = null;

	/**
	 * Handles database connection and knows how to handle transactions.
	 *
	 * @var Core\TransactionalManager
	 */
	protected $transactionalManager = null;

	/**
     * The metadata factory, used to retrieve the OGM metadata of domain objects.
     *
     * @var Meta\MetaObject
     */
    private $metaFactory = null;

    /**
     * The proxy factory used to create dynamic proxies.
     *
     * @var Proxy\ProxyFactory
     */
    private $proxyFactory = null;
    /**
     * The repository factory used to create dynamic repositories.
     *
     * @var Repositories\RepositoryFactory
     */
    private $repositoryFactory = null;

	public function __construct(TransactionalManager $transactionalManager){

		$this->transactionalManager = $transactionalManager;

		$this->identityMap = new Collection();
		$this->managedObjects = new Collection();
		$this->removedObjects = new Collection();

		// TODO: inject a meta factory, do not create statically;
		$this->metaFactory = new MetaFactory();

	}

	public function getManager(){

		return $this->transactionalManager;

	}

	public function commit(){

		/**
		 * Looping through all managed objects and cascade persist;
		 * keep in mind that we work only with MetaObject's
		 */
		$managed = new Collection();
		foreach ($this->managedObjects as $metaObject) {
			$this->cascadeManage($metaObject, $managed);	
		}
		
		foreach ($managed as $key => $metaObject) {
			switch ( $metaObject->getState() ) {
					case ObjectState::STATE_NEW:
						$this->getMapper($metaObject)->insert($metaObject);
						break;
					
					default:
						# code...
						break;
				}	
		}

		$this->transactionalManager->flush();

	}

	/**
	 * Loops through all the associations of the domain object
	 * and manages the objects attached until all the graph associated is managed;
	 * a $managed collection will return the graph filled with all objects in the graph.
	 * We can only cascade objects of type MetaObject that promise consistency;
	 *
	 * @param Core\MetaObject
	 * @param Core\Collection
	 * @return Core\Collection
	 */
	public function cascadeManage(MetaObject $object, Collection $managed){
		
		// if the holding object is a proxy and is not initialized, ignore;
		if( $object->getProxy() && ! $object->getProxy()->__isInitialized() ){
			return ;
		}

		$hash = $this->getObjectHash($object);
		if( $managed->get($hash) ){
			return ;
		}

		// TODO: check if in removed objects

		$managed->set($hash, $object);
		
		foreach ($object->getAssociations() as $association) {

			// if collection iterate over all objects attached and recursively cascade manage
			if($association instanceof Collection){
				
				foreach ($association as $object) {

					$meta = $this->getMetaObject($object);
					$this->cascadeManage($meta, $managed);

				}

				continue;

			}

			// if not collection then get cascade manage only the associated object
			$meta = $this->getMetaObject($association);
			$this->cascadeManage($meta, $managed);

		}

	}

	 /**
     * Persists a domain object as part of the current unit of work.
     *
     * @param object $object The domain object to persist.
     * @return this
     * @throws Core\OGMException
     */
	public function persist($object){

		if( ! is_object($object) ){
			throw new OGMException('You are not allowed to persist something that is not an object.');
		}

		// first we get the meta object, this operation will also check that the object provided
		// can be persisted eg. has the correct annotations; from now on we will work only with 
		// meta objects for better consistency and type-checking;s
		$metaObject = $this->getMetaObject($object);
		$hash = $this->getObjectHash($metaObject);

		// if the object is already managed do nothing
		if( $this->isManaged($metaObject) ){
			return ;
		}

		// if the object is scheduled to be removed from the db
		// delete it from scheduler and proceed with adding it again to managed objects
		if( $this->hasBeenRemoved($metaObject) ){
			$this->removedObjects->remove($hash);
		}
	
		$this->managedObjects->set($hash, $metaObject);
		return $this;

	}

	// TODO:
	public function persistCollection(){
		// If the object persisted is iterable then we loop over all
		// domain objects inside the collection and add them to managed objects;
		// the managed domain objects contains only meta objects for better
		// encapsulation, consistency and type check
		if($object instanceof Collection){
			
			$collection = $object;
			foreach ($collection as $value) {

				$meta = $this->getMetaObject($value);
				$this->managedObjects->set(spl_object_hash($meta), $meta);
				
			}

			return $this;
		}
	}

	/**
	 * Checks whether the domain object is being managed by this unit of work; make sure
	 * to first get the meta object using getMetaObject();
	 *
	 * @return boolean
	 */
	public function isManaged($object){

		$hash = $this->getObjectHash($object);
		if( $this->managedObjects->get($hash) ){
			return true;
		}

		return false;

	}

	/**
	 * Checks whether the domain object provided is scheduled to be removed.
	 *
	 * @return boolean
	 */
	public function hasBeenRemoved($object){

		$hash = $this->getObjectHash($object);
		if( $this->removedObjects->get($hash) ){
			return true;
		}

		return false;

	}

	protected function getObjectHash($object){

		// If we passed a meta object then the hash is the wrapped object's hash
		if($object instanceof MetaObject){
			$hash = $object->getHash();
		}else{
			$hash = spl_object_hash($object);
		}

		return $hash;

	}

	/**
	 * Gets the meta object from a domain object.
	 * 
	 * @param DomainObject | Proxy\ValueHolder
	 * @return Meta\MetaObject
	 */
	public function getMetaObject($object){

		if( ! is_object($object) ){
			throw new OGMException('You cannot get the meta object for something that is not an object.');
		}

		// if the object provided is already instance of MetaObject return it
		if( $object instanceof MetaObject ){
			return $object;
		}

		$meta = $this->metaFactory->getMetaClassFor($object);
		return $meta;

	}

	public function getMapper($object){

		$meta = $this->getMetaObject($object);
		$namespace = $meta->getMapperNamespace();

		return new $namespace($this);

	}

}