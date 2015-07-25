<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/**
 * Collection for domain objects
 * // TODO: implements \Countable, \IteratorAggregate, \ArrayAccess
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class DomainObjectCollection {

	/**
	 * All the objects that belong to this collection will be stored here
	 * having the hash id (spl_object_hash) as the key
	 *
	 * @var array(QNetwork\Infrastructure\OGM\Core\DomainObject)
	 */
	protected $objects = [];

	/** 
	 * All the objects that have been removed from collection.
	 * To Be Decided ((! Be aware that objects can be removed if they don't exist,
	 * in case of database removals))
	 *
	 * @var array(QNetwork\Infrastructure\OGM\Core\DomainObject)
	 */
	protected $removedObjects = [];

	/**
	 * Knows if this collection has been loaded from DB
	 * 
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Returns all objects in this collection as an array of objects.
	 *
	 * @return array(spl_object_hash, DomainObject)
	 */
	public function get(){

		return $this->objects;

	}

	/**
	 * Returns all removed objects from this collection.
	 *
	 * @return array(spl_object_hash, DomainObject)
	 */
	public function getRemovedObjects(){

		return $this->removedObjects;

	}

	/** 
	 * @param QNetwork\Infrastructure\OGM\Core\DomainObject
	 */
	public function add(DomainObject $object){

		$id = spl_object_hash($object);
		$this->objects[$id] = $object;

		return $this;

	}

	/**
	 * @param spl_object_hash
	 * @return QNetwork\Infrastructure\OGM\Core\DomainObject | null
	 */
	public function retrieve($id){

		if( ! isset($this->objects[$id]) ){

			return null;
			
		}

		return $this->objects[$id];

	}

	/**
	 * Tries to remove an object from the collection; if the object doesn't exist
	 * false will be returned; if the object is removed successfully true will be returned
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\DomainObject
	 * @return boolean
	 */
	public function remove(DomainObject $object){

		$id = spl_object_hash($object);

		if( ! isset($this->objects[$id]) ){

			return false;

		}

		$this->objects[$id] = null;
		$this->removedObjects[$id] = $object;

		return true;

	}

	/**
	 * Returns the number of elements in this collection
	 *
	 * @return int
	 */
	public function count(){

		return count($this->objects);

	}

	/**
	 * Checks wheather a collection is empty or not
	 *
	 * @return boolean
	 */
	public function isEmpty(){

		if( $this->count() === 0 && count($this->getRemovedObjects()) === 0){
			return true;
		}

		return false;
		
	}

	/**
	 * Removes all objects from the map
	 *
	 * @return void
	 */
	public function clear(){

		$this->objects = [];
		$this->removedObjects = [];

		return $this;
		
	}

	public function setLoaded($loaded = true){

		if( ! is_bool($loaded) ){
			throw new OGMException('You are trying to set a collection as loaded without using a boolean.');
		}

		$this->loaded = $loaded;

	}

	public function loaded(){

		return $this->loaded;

	}

}