<?php 
namespace Mapping;
use Core\OGMException;
use Core\ObjectState;

abstract class NodeMapper extends AbstractMapper{

	use CypherTrait, NodeFinder;

	abstract public function match($object, $name = 'value');

	/**
	 * Takes an array of (key, value) pairs representing the node's properties
	 * and tranforms it into a single domain object without any related entities.
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
	public function doLoad($properties){
		
		if( ! isset($properties['_class']) ){
			throw new OGMException('The node provided does not have the property _class and the domain object cannot be instantiated.');
		}

		/**
		 * We will be creating a new instance of our domain object without using the constructor
		 * and we will be using reflection to set the required properties.
		 */
		$class = $properties['_class'];
		$instance = NodeReflector::newInstanceWithoutConstructor($class);
		
		$graphProperties = NodeReflector::getGraphProperties($class);
		foreach ($graphProperties as $property) {
			
			if( isset($properties[$property->key]) ){

				$propertyValue = $properties[$property->key];
				if($property->reference){
					$reference = $property->reference;
					$propertyValue = new $reference($propertyValue);
				}

				NodeReflector::setPropertyValueForObject($instance, $property->propertyName, $propertyValue);
			}

		}
		
		$associations = NodeReflector::getAssociations($class);
		foreach ($associations as $value) {
			
			if($value->collection){
				$statement = $this->match($instance);
				$statement[0] .= "-[:{$value->type}]->(result) RETURN result SKIP 0 LIMIT 100";

				$proxy = new Collection($statement);
			}else{
				$statement = $this->match($instance);
				$statement[0] .= "-[:{$value->type}]->(result) RETURN result";
				
				$proxy = new ValueHolder($statement);
			}
			
			NodeReflector::setPropertyValueForObject($instance, $value->propertyName, $proxy);

		}
		
		return $instance;

	}

	public function mergeAllRelationships($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		$annotations = $meta->getAssociations();
		$values = $meta->getAssociations($object);
		
		foreach ($values as $propertyName => $value) {
			
			$annotation = $annotations[$propertyName];
			if( is_array($value) || $value instanceof \Traversable ){
				
				foreach ($value as $collectionValue) {
					$this->mergeRelationship($object, $collectionValue, $annotation->type, $annotation->direction);
				}
				// dd($this->getUnitOfWork()->getManager());
				continue;

			}

			$this->mergeRelationship($object, $value, $annotation->type, $annotation->direction);

		}

	}

	/**
	 * Merges a relationship between 2 nodes $from and $to
	 *
	 * @param DomainObject the start node
	 * @param DomainObject the end node
	 * @param string the type of the relationship
	 * @return void
	 */
	protected function mergeRelationship($from, $to, $type, $direction){
		
		list($matchFrom, $paramsFrom) = $this->getUnitOfWork()->getMapper($from)->match($from, 'from');
		list($matchTo, $paramsTo) = $this->getUnitOfWork()->getMapper($to)->match($to, 'to');

		$params = array_merge($paramsFrom, $paramsTo);

		$query = $matchFrom . ' ' . $matchTo;
		$query .= " MERGE (from)-[r:{$type}]->(to) ";

		$this->addRelationshipStatement($query, $params);

	}

	/**
	 * Deletes the $type relationship between 2 nodes $from and $to .
	 * The OGM ensures by default that there is only one relationship
	 * of a given type between any 2 given domain objects.
	 *
	 * @param DomainObject the start node
	 * @param DomainObject the end node
	 * @param string the type of the relationship
	 * @return void
	 */
	protected function deleteRelationship($from, $type, $to = null){

		list($query, $params) = $this->match($from, 'from');
		
		if( $to ){

			list($toQuery, $toParams) = $this->match($to, 'to');
			$query .= ' ' . $toQuery;
			$params = array_merge($params, $toParams);

			$query .= " MATCH (from)-[r:{$type}]->(to) DELETE r";
		}

		if( ! $to ){
			$query .= " MATCH (from)-[r:{$type}]->() DELETE r";
		}

		$this->addRelationshipStatement($query, $params);

	}

	protected function getNodeProperties($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		
		// Gets all properties annotations (GraphProperty annotation) and their values present on object
		$annotations = $meta->getProperties();
		$values = $meta->getProperties($object);

		// map the annotations and values to a [key, property] array for Nodes
		$properties = [];
		foreach ($values as $propertyName => $value) {
			$properties[$annotations[$propertyName]->key] = $value;
			settype($properties[$annotations[$propertyName]->key], $annotations[$propertyName]->type);
		}
		
		return $properties;

	}

}