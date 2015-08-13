<?php 
namespace Mapping;
use Core\MetaObject;
use Core\OGMException;
use Proxy\ValueHolder;
use Proxy\Proxy;
use Core\Collection;
use Meta\Node as NodeMetaObject;
use Core\ObjectState;

/**
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
class NodeMapper extends AbstractMapper{

	use CypherTrait, NodeFinder;

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

	/**
	 * Merges a relationship between 2 nodes $from and $to
	 *
	 * @param DomainObject the start node
	 * @param DomainObject the end node
	 * @param string the type of the relationship
	 * @return void
	 */
	protected function mergeRelationship($from, $to, $type){

		list($matchFrom, $paramsFrom) = $this->match($from, 'from');
		list($matchTo, $paramsTo) = $this->match($to, 'to');

		$params = array_merge($paramsFrom, $paramsTo);

		$query = $matchFrom . ' ' . $matchTo;
		$query .= " MERGE (from)-[r:{$type}]->(to) ";

		$this->addRelationshipStatement($query, $params);

	}

	public function updateRelationships(NodeMetaObject $object){

		$associations = $object->getAssociations();

		foreach ($associations as $value) {
			
			// If the association is a collection, loop throw all objects in collection
			// get the statements and continue to next value
			if($value instanceof Collection){
				dd($value);
				$collection = $value;
				foreach ($collection as $collectionObject) {

					$statement = $this->getMergeRelationshipStatement($collectionObject);
					$this->addRelationshipStatement($statement[0], $statement[1]);

				}
				continue;

			}

			$statement = $this->getMergeRelationshipStatement($value);
			$this->addRelationshipStatement($statement[0], $statement[1]);

		}

	}

	public function getMergeRelationshipStatement(NodeMetaObject $from, NodeMetaObject $to){
		dd($object);
		// if the value attached is a proxy do nothing as the relationship already exists
		if($value instanceof Proxy){
			return null;
		}

		// if it is not a Proxy and the object is an entity
		// then it is a new value and we insert the relationship

		// if it is not a Proxy and the object is a value object
		// then we merge the relationship	

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

	/**
	 * Creates the statement that sets the node properties for a specific object that is defined as Node.
	 * If it is a NEW entity then we CREATE the entity, set the _class property and created_at;
	 * If it is a DIRTY entity then we just MATCH and SET and updated_at;
	 *
	 * @param Meta\Node
	 * @return void
	 */
	protected function getStatementForSettingNodeProperties(NodeMetaObject $object){

		$class = $object->getClass();
		
		/**
		 * get labels and the node properties as values defined by metadata;
		 * the node properties are the properties on the object with Annotations\GraphProperty
		 */
		$labels = $object->getLabels();
		$labels = $this->mapLabelsToCypher($labels);
		$properties = $object->getProperties();

		/**
		 * Define params as merged properties and created date / update date
		 */
		switch( $object->getState() ){
			case ObjectState::STATE_NEW:
			$params = [ 'created_at' => 'todo', '_class' => $class ];
			$params = array_merge($properties, $params);
			$properties = $this->mapPropertiesToCypher($params);

			$query = "CREATE (value:{$labels}) SET {$properties}";
			break;

			case ObjectState::STATE_DIRTY;
			$params = [ 'updated_at' => 'todo' ];
			$params = array_merge($properties, $params);
			$properties = $this->mapPropertiesToCypher($params);

			$query = "MATCH (value:{$labels} { id: {id} }) SET {$properties}";
			break;

			default:
			throw new OGMException('You are trying to set the node properties but the state provided is invalid.');
			break;
		}

		return [$query, $params];

	}

}