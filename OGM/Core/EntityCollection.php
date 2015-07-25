<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class EntityCollection extends DomainObjectCollection{

	/**
	 * This provides a map for Entity id
	 * 
	 * @var array
	 */
	protected $ids = [];

	public function getEntityIds(){

		return $this->ids;

	}
	
	/**
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 */
	public function add(DomainObject $object){

		if( ! $object instanceof Entity ){
			throw new OGMException('You are trying to attach a non entity to a EntityCollection.');
		}

		parent::add($object);
		
		$entityId = (string) $object->getId();
		$objectId = spl_object_hash($object);
		$this->ids[$objectId] = $entityId;

		return $this;

	}

	/**
	 * @param QNetwork\Infrastructure\OGM\Core\Id
	 * @return QNetwork\Infrastructure\OGM\Core\Entity | null
	 */
	public function retrieveByEntityId(Id $id){

		$entityId = (string) $id;
		$objectId = array_search($entityId, $this->ids);

		if( ! $objectId || ! isset($this->objects[$objectId]) ){

			return null;
			
		}

		return $this->objects[$objectId];

	}

	public function remove(DomainObject $object){
		
		if( ! $object instanceof Entity ){
			throw new OGMException('You are trying to remove a non entity from a EntityCollection.');
		}

		if( ! parent::remove($object) ){
			return false;
		}

		$objectId = spl_object_hash($object);
		$this->ids[$objectId] = null;

		return true;

	}

	public function clear(){

		parent::clear();
		$this->ids = [];

		return $this;

	}

}