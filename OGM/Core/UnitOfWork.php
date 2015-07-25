<?php 
namespace QNetwork\Infrastructure\OGM\Core;
use QNetwork\Infrastructure\OGM\Meta\MetaMapping;
use QNetwork\Infrastructure\InfrastructureException;
use QNetwork\Infrastructure\OGM\Annotations\OneToOne;
use QNetwork\Infrastructure\OGM\Annotations\OneToMany;
use QNetwork\Infrastructure\OGM\Reflection\Reflector;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use QNetwork\Infrastructure\OGM\Mappers\DomainObjectCollectionMapper;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class UnitOfWork {

	/**
     * The identity map that holds references to all managed domain objects that have
     * an identity (Entities); check ddd; if an object that already exists in-memory
     * is being pulled from persistence then a reference to the in-memory object 
     * will be returned.
     *
     * @var QNetwork\Infrastructure\OGM\Core\IdentityMap
     */
	protected $identityMap = null;

	/**
	 * Map of the original entity data of managed entities.
	 * At commit time all managed entities will be compared with 
	 * the original entities to determine the updates that need
	 * to happen; 
	 *
	 * Internal note: Note that PHPs "copy-on-write" behavior helps a lot with memory usage.
     *                A value will only really be copied if the value in the entity is modified
     *                by the user.
     *
     * @var QNetwork\Infrastructure\OGM\Core\EntityCollection
     */
	protected $originalEntities = null;

	/** 
	 * All the domain objects that are being managed; the persist method has
	 * been used on them.
	 *
	 * @var QNetwork\Infrastructure\OGM\Core\DomainObjectCollection
	 */
	protected $managedDomainObjects = null;

	/**
	 * All the domain objects that are set to be removed at commit; the remove
	 * method has been used on them
	 *
	 * @var QNetwork\Infrastructure\OGM\Core\DomainObjectCollection
	 */
	protected $removedDomainObjects = null;

	/**
	 * Handles the client/connection with the database/databases
	 * and takes care of the transactions.
	 *
	 * @var QNetwork\Infrastructure\OGM\Core\TransactionalManager
	 */
	protected $manager = null;

	/**
	 * TODO: re-think this
	 */
	protected $collectionMapper = null;

	public function __construct(TransactionalManager $manager){

		$this->manager = $manager;
		Reflector::registerAnnotations();

		$this->identityMap = new IdentityMap();
		$this->originalEntities = new EntityCollection();
		$this->managedDomainObjects = new DomainObjectCollection();
		$this->removedDomainObjects = new DomainObjectCollection();

	}

	public function getManager(){

		return $this->manager;

	}

	public function getIdentityMap(){

		return $this->identityMap;

	}

	public function getManagedObjects(){

		return $this->managedDomainObjects;

	}

	public function getRemovedObjects(){

		return $this->removedDomainObjects;

	}

	public function persist(DomainObject $object){

		/**
		 * if object is already managed do nothing
		 */
		if( $this->isManaged($object) ){

			return ;

		}

		/**
		 * if object has been removed, clear it from removed objects
		 */
		if( $this->hasBeenRemoved($object) ){

			$this->removedDomainObjects->remove($object);

		}

		$this->managedDomainObjects->add($object);

		return $this;

	}

	protected function addToIdentityMap(DomainObject $object){

		if( $object->isEntity() ){

			$this->identityMap->add($object);

		}

		return $this;

	}

	/**
	 * Checks whether the domain object is being managed by this unit of work;
	 * persist() method has been applied on it;
	 *
	 * @return boolean
	 */
	public function isManaged(DomainObject $object){

		$id = spl_object_hash($object);
		if( $this->managedDomainObjects->retrieve($id) ){

			return true;

		}

		return false;

	}

	/**
	 * Checks whether the domain object has been removed; 
	 * remove() method has been applied on it.
	 *
	 * @return boolean
	 */
	public function hasBeenRemoved(DomainObject $object){

		$id = spl_object_hash($object);
		if( $this->removedDomainObjects->retrieve($id) ){

			return true;

		}

		return false;

	}

	public function remove(DomainObject $object){

		/**
		 * if it has been removed already ignore this operation
		 */
		if($this->hasBeenRemoved($object)){
			return;
		}

		/** 
		 * remove from managed domain objects
		 * add to removed domain objects
		 */
		$this->managedDomainObjects->remove($object);
		$this->removedDomainObjects->add($object);

		return $this;

	}

	public function clear(){

		$this->identityMap = new IdentityMap();
		$this->originalEntities = new EntityCollection();
		$this->managedDomainObjects = new DomainObjectCollection();
		$this->removedDomainObjects = new DomainObjectCollection();

	}

	/**
     * Commits the UnitOfWork, executing all operations that have been postponed
     * up to this point. The state of all managed entities will be synchronized with
     * the database.
     *
     * The operations are executed in the following order:
     *
     * 1) All entity insertions
     * 2) All entity updates
     * 3) All collection deletions
     * 4) All collection updates
     * 5) All entity deletions
     */
	public function commit(){
		
		$this->cascadePersistAllManagedEntities();
		$managed = array_merge(
			$this->removedDomainObjects->get(), 
			$this->managedDomainObjects->get()
			);
		// dd($managed);
		foreach ($managed as $splId => $object) {
			
			/**
			 * if object is an entity then get the state
			 * if state NEW then INSERT
			 * if state DIRTY then UPDATE
			 * if state CLEAN do nothing
			 * if state REMOVED then DELETE
			 */
			if( Reflector::isEntity($object) ){

				$this->updateEntity($object);
				
			}

			/**
			 * if object is a ValueObject then MERGE
			 */
			if( ! Reflector::isEntity($object) ){

				$this->updateValueObject($object);
				
			}

		}
		// dd($this->getManager());
		$this->getManager()->commit();

	}

	private function cascadePersistAllManagedEntities(){

		/**
		 * We first copy all managed entities into a local variable
		 * We then clear the managed entities object so 
		 * we can cascade persist without worrying about conflicts
		 */
		$managed = $this->managedDomainObjects->get();
		$this->managedDomainObjects->clear();

		/**
		 * looping through all managed entities and cascade persist each one
		 */
		foreach ($managed as $splId => $object) {
			
			$this->cascadePersist($object);

		}

	}

	/**
	 * Cascades the persist operation to associated entities
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\DomainObject
	 * @return void
	 */
	private function cascadePersist(DomainObject $object){

		$this->persist($object);

		$oneToOne = Reflector::getOneToOneAssociationsAsValues($object);
		$oneToMany = Reflector::getOneToManyAssociationsAsValues($object);
		
		foreach ($oneToOne as $object) {

			if( $object !== null ){
				$this->cascadePersist($object);
			}

		}
		
		foreach ($oneToMany as $collection) {
			
			foreach ($collection->get() as $key => $object) {

				if( $object !== null ){
					$this->cascadePersist($object);
				}

			}
		}

	}

	/**
	 * if object is an entity then get the state
	 * if state NEW then INSERT
	 * if state DIRTY then UPDATE
	 * if state CLEAN do nothing
	 */		
	private function updateEntity(Entity $entity){

		$state = $this->getState($entity);
		$mapper = $entity::getMapper();
		
		switch ($state) {
			case State::__default:
				$mapper->insert($entity);
				$mapper->updateRelationships($entity);
				break;
					
			case State::DIRTY:
				$mapper->update($entity);
				$mapper->updateRelationships($entity);
				break;

			case State::CLEAN:
				$mapper->updateRelationships($entity);
				break;

			case State::REMOVED:
				$mapper->delete($entity);
				break;

			default:
				throw new OGMException('Unexpected entity state: '. $state);
				break;

		}

	}

	/**
	 * Updates(merge) a value object into DB in the scope of a transaction
	 * 
	 * @return void
	 */
	private function updateValueObject(ValueObject $object){

		$mapper = $object::getMapper();
		$mapper->merge($object);
		$mapper->updateRelationships($object);

	}

	/** 
	 * Gets the state (CLEAN, NEW, UPDATED, DELETED) of an Entity in order to 
	 * know which mapper method needs to be applied
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @return QNetwork\Infrastructure\OGM\Core\State
	 */
	public function getState(Entity $entity){

		/**
		 * If entity is present in removed domain objects then 
		 * the entity should be deleted from the persistence store
		 */
		$removed = $this->removedDomainObjects->retrieve(spl_object_hash($entity));
		if($removed !== null){

			return State::REMOVED;

		}

		/**
		 * if the original entity is empty that means our current entity
		 * is new and should be created into DB
		 */
		$original = $this->getOriginalEntity($entity->getId());
		if($original === null){

			return State::__default;

		}

		/**
		 * The object is DIRTY if any properties on it are different
		 * than the original properties on it
		 */
		if($original != $entity){

			return State::DIRTY;

		}

		return State::CLEAN;

	}

	/**
	 * It cleans the entity; checks to see if it already exists 
	 * in identity map, if so it returns the entity already existent discovering 
	 * the new created entity; the receiver should continue using that one;
	 * 
	 */
	public function clean(Entity $entity){

		$fromMap = $this->getIdentityMap()->retrieveByEntityId( $entity->getId() );
		if($fromMap){

			return $fromMap;

		}

		$this->persist($entity);
		$this->getIdentityMap()->add($entity);

		$clone = clone $entity;
		$this->originalEntities->add($clone);

		return $entity;
		
	}

	/**
	 * Looks up the meta class for $object and returns it
	 *
	 * @return QNetwork\Infrastructure\OGM\Meta\MetaMapping
	 */
	public function getMetaClass(DomainObject $object){

		return MetaMapping::getMetaClass($object);

	}

	public function getOriginalEntity(Id $id){

		return $this->originalEntities->retrieveByEntityId($id);

	}

	public function getRepository($class){

		$reflector = Reflector::getReflectorClass($class);
		$repository = $reflector::getRepository($class);
		
		return \App::make($repository, [$this, $class]);

	}

	public function getMapper($class){

		$reflector = Reflector::getReflectorClass($class);
		$repository = $reflector::getMapper($class);
		
		return \App::make($repository, [$this, $class]);

	}

	public function getCollectionMapper(){

		if( ! isset($this->collectionMapper) ){
			$this->collectionMapper = new DomainObjectCollectionMapper($this);
		} 

		return $this->collectionMapper;

	}
	
}