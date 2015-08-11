<?php 
namespace Mapping;
use Core\MetaObject;
use Core\OGMException;
use Proxy\ValueHolder;
use Core\Collection;
use Meta\Node as NodeMeta;
use Core\ObjectState;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class NodeMapper extends AbstractMapper{

	// abstract protected function load($results);

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

	public function match($object, $name = "value"){

		/**
		 * Loops through $object meta and detects which graph properties
		 * are being used for the match (id for Entity and merging properties for ValueObject)
		 */
		$params = NodeReflector::getMatchPropertiesAsValues($object);
		$labels = NodeReflector::getLabels($object);

		$labels = $this->mapLabelsToCypher( $labels );
		// $matchProperties = $this->mapPropertiesToCypherForMatch( $params, $name, true );
		list($matchProperties, $params) = $this->mapPropertiesTEST($params);
		$query = "MATCH ($name:{$labels} {{$matchProperties}})";
		
		return [$query, $params];

	}

	protected function mapPropertiesTEST($properties){

		$cypher = '';
		$newParams = [];

		foreach ($properties as $property => $value) {
			
			$key = uniqid() . '_' . $property; 
			$newParams[$key] = $value;

			$cypher .= "$property: {" . $key ."},";

		}
		
		$cypher = rtrim($cypher, ",");
		return [$cypher, $newParams];

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

	/**
	 * Loops through all OneToOne properties that this object has
	 * and inserts the relationship between $entity and the end object
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @return void
	 */
	protected function insertOneToOneRelationships($entity){

		$oneToOne = NodeReflector::getOneToOneAssociationsAsValues($entity);
		
		foreach ($oneToOne as $relationshipType => $object) {
			$this->mergeRelationship($entity, $object, $relationshipType);
		}

	}

	/**
	 * Loops through all OneToMany properties that this object has
	 * and inserts the relationship between $entity and the end objects
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @return void
	 */
	protected function insertOneToManyRelationships($entity){

		$oneToMany = NodeReflector::getOneToManyAssociationsAsValues($entity);
		
		foreach ($oneToMany as $relationshipType => $collection) {
			
			foreach ($collection->get() as $object) {
				$this->mergeRelationship($entity, $object, $relationshipType);
			}

		}

	}

	public function updateRelationships($object){

		$this->updateOneToOneRelationships($object);
		$this->updateOneToManyRelationships($object);

	}

	/**
	 * Loops through all the OneToOne properties of $entity and checks
	 * them against the $original object (usually pulled from DB or cleaned).
	 * If any property is different than the old relationship is being deleted
	 * while a new one pointing to the new object will be inserted.
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @return void
	 */
	protected function updateOneToOneRelationships($entity){

		$oneToOne = NodeReflector::getOneToOneAssociationsAsValues($entity);
		
		foreach ($oneToOne as $relationshipType => $object){

			// DELETE relationship
			$this->deleteRelationship($entity, $relationshipType);

			// INSERT relationship 
			$this->mergeRelationship($entity, $object, $relationshipType);

		}
		
	}

	protected function updateOneToManyRelationships($entity){
		
		$oneToMany = NodeReflector::getOneToManyAssociationsAsValues($entity);

		foreach ($oneToMany as $relationshipType => $collection) {
			
			if( $collection->isEmpty() ){
				continue;
			}
			
			/*foreach ($collection->getRemovedObjects() as $object) {
				// DELETE relationship
				$this->deleteRelationship($entity, $relationshipType, $object);
			}*/

			foreach ($collection as $object) {
				// DELETE relationship
				// $this->deleteRelationship($entity, $relationshipType, $object);

				// INSERT relationship 
				$this->mergeRelationship($entity, $object, $relationshipType);
			}			

		}
		
	}

	/**
	 * Transform an array of labels to cypher
	 *
	 * @param array
	 * @return string
	 */
	public function mapLabelsToCypher($labels){

		$cypherLabels = '';
    	foreach ($labels as $label) {
			
			$cypherLabels .= $label . ':';

		}

		$cypherLabels = rtrim($cypherLabels, ":");

		return $cypherLabels;

	}

	/**
	 * Deletes the $type relationship between 2 nodes $from and $to .
	 * The QNetwork\OGM ensures by default that there is only one relationship
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

		// echo $query;die();
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
	protected function getStatementForSettingNodeProperties(NodeMeta $object){

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