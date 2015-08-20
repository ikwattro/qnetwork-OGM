<?php 
namespace Mapping;
use Meta\NodeEntity as MetaNodeEntity;
use Proxy\ProxyFactory;
use Core\Collection;

class NodeFinder extends AbstractMapper{

	/**
	 * Takes an array of properties and loads the domain object.
	 * The array should has to contain _class otherwise an exception will be thrown;
	 * For entities the array should contain an id an this will be checked against the identity map;
	 *
	 * @param array
	 */
	public function load($properties){

		$object = $this->doLoad( $properties );
		$meta = $this->getUnitOfWork()->getClassMetadata($object);

		if($meta instanceof MetaNodeEntity){

			$id = $meta->getId($object);
			$fromMap = $this->getUnitOfWork()->getIdentityMap()->get($id);
			if($fromMap){
				return $fromMap;
			}

			$this->getUnitOfWork()->clean($object);

		}
		
		return $object;

	}

	/**
	 * Takes an array of (key, value) pairs representing the node's properties
	 * and tranforms it into a single domain object with all associated domain objects as proxies.
	 *
	 * This method uses the _class property on the node to create the object needed.
	 * No logic involving cleaning the object, checking the identity map or checking
	 * if the class assigned to this mapper is the one in _class. All this logic 
	 * should be present in the abstract method load() that will be implemented by sub-classes.
	 *
	 * This way we will be able to use the doLoad method in the same mapper for other domain objects,
	 * this way making the eager loading and polymorphic relationships easier to implement.
	 *
	 * @param array(key, value) An array of parameters for creating the object. The _class parameter should be present.
	 * @return DomainObject
	 */
	protected function doLoad($properties){
		
		if( ! isset($properties['_class']) ){
			throw new OGMException('You cannot load a domain object using an array of properties that does not has the _class property.');
		}

		/**
		 * We will be creating a new instance of our domain object without using the constructor
		 * and we will be using reflection to set the required properties.
		 */
		$class = $properties['_class'];
		$meta = $this->getUnitOfWork()->getClassMetadata($class);

		$instance = $meta->newInstanceWithoutConstructor();
		
		$annotations = $meta->getProperties();
		foreach ($annotations as $annotation) {
			
			// If the annotation property is present on the $properties array passed
			$key = $annotation->key;
			if( isset($properties[$key]) ){
				
				// instantiate object is value object as property
				$propertyValue = $properties[$key];
				if($annotation->reference){
					$reference = $annotation->reference;
					$propertyValue = new $reference($propertyValue);
				}
				
				$meta->getReflector()->setPropertyValueForObject($instance, $annotation->propertyName, $propertyValue);

			}

		}
		
		if($meta instanceof MetaNodeEntity){
			$meta->setId($instance, $properties[$meta->getId()->propertyName]);
		}
		
		$associations = $meta->getAssociations();
		
		foreach ($associations as $value) {
			
			if($value->collection){

				$proxy = new \Core\LazyCollection();
				$statement = $this->getUnitOfWork()->getMapper($meta->getClass())->match($instance);
				$statement[0] .= "-[:{$value->type}]->(result) RETURN result ";

				$proxy->__setAssociatedObject($instance);
				$proxy->__setAnnotation($value);
				$proxy->__setStatementToGetValue($statement);
				$proxy->__setFinder($this);

			}else{

				$proxyFactory = new ProxyFactory();
				$initializer = 
					function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use($meta, $instance, $value){
						
						// instantiation logic here
						$statement = $this->getUnitOfWork()->getMapper($meta->getClass())->match($instance);
						$statement[0] .= "-[:{$value->type}]->(result) RETURN result";
    					$wrappedObject = $this->getSingle($statement[0], $statement[1]);
    					
						// turning off further lazy initialization
        				$initializer   = null; 
    				};
    			
				$proxy = $proxyFactory->createFromDomainObject($value->reference, $initializer);
				
			}
			
			$meta->getReflector()->setPropertyValueForObject($instance, $value->propertyName, $proxy);
			
		}
		
		return $instance;

	}

	/**
	 * This mapping function returns a single domain object that is represented by a node in the database.
	 * All the domain objects attached to this one will be represented as proxies.
	 *
	 * @param string The query that will be run
	 * @param params The params that are being passed to the query
	 * @return DomainObject
	 */
	public function getSingle($query, $params = []){

		$resultSet = $this->getResultSet($query, $params);
		
		/**
		 * If the result set is empty, return null
		 */
		if( ! count($resultSet->getNodes()) ){
			return null;
		}

		if( count($resultSet->getNodes()) > 1){
			throw new OGMException('The provided query is invalid. query: ' . $query);
		}

		return $this->load( $resultSet->getSingleNode()->getProperties() );

	}

	/**
	 * This mapping function returns a single domain object that is represented by a node in the database.
	 * All the domain objects attached to this one will be represented as proxies.
	 *
	 * @param string The query that will be run
	 * @param params The params that are being passed to the query
	 * @return DomainObject
	 */
	public function getCollection($query, $params = []){

		$resultSet = $this->getResultSet($query, $params);
		
		/**
		 * If the result set is empty, return null
		 */
		if( ! count($resultSet->getNodes()) ){
			return null;
		}

		$collection = new Collection();
		foreach ($resultSet->getNodes() as $node) {
			$collection->add( $this->load($node->getProperties()) );
		}

		return $collection;

	}

}
