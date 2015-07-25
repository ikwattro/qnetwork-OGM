<?php 
namespace QNetwork\Infrastructure\OGM\Mappers;
use QNetwork\Infrastructure\OGM\Core\DomainObject;
use QNetwork\Infrastructure\OGM\Core\DomainObjectCollection;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use QNetwork\Infrastructure\OGM\Reflection\NodeReflector;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
abstract class NodeMapper extends AbstractMapper{

	abstract protected function load($results);

	public function doLoad($node){
		
		if( ! $node->hasProperty('_class') ){
			throw new OGMException('The node provided does not have the property _class and cannot be instantiated');
		}

		$class = $node->getProperty('_class');
		$instance = NodeReflector::newInstanceWithoutConstructor($class);
		
		/**
		 * Because we create our instance without a construtor,
		 * some dependencies that are being added in constructor are being
		 * moved into a separat constructorDependencies method.
		 * eg. defining a property as a collection
		 */
		if( method_exists($instance, 'constructDependencies') ){
			$instance->constructDependencies();
		}

		$graphProperties = NodeReflector::getGraphProperties($this->class);
		$nodeProperties = $node->getProperties();
		
		foreach ($graphProperties as $property) {
			
			if( isset($nodeProperties[$property->key]) ){

				$propertyValue = $nodeProperties[$property->key];
				if($property->reference){
					$reference = $property->reference;
					$propertyValue = new $reference($propertyValue);
				}

				NodeReflector::setPropertyValueForObject($instance, $property->propertyName, $propertyValue);
			}

		}
		// dd($instance);
		return $instance;

	}

	public function findSingle($query, $params = []){

		$results = $this->getNodesFromQuery($query, $params);
		
		/**
		 * If the result set is empty, return null
		 */
		if( ! count($results->getNodes()) ){
			return null;
		}

		$object = $this->load($results);
        
        return $object;

	}

	public function findCollection($query, $params = []){

		return $this->getUnitOfWork()->find($query, $params);
		
	}

	public function match(DomainObject $object, $name = "value"){

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
	protected function mergeRelationship(DomainObject $from, DomainObject $to, $type){

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
	protected function insertOneToOneRelationships(DomainObject $entity){

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
	protected function insertOneToManyRelationships(DomainObject $entity){

		$oneToMany = NodeReflector::getOneToManyAssociationsAsValues($entity);
		
		foreach ($oneToMany as $relationshipType => $collection) {
			
			foreach ($collection->get() as $object) {
				$this->mergeRelationship($entity, $object, $relationshipType);
			}

		}

	}

	public function updateRelationships(DomainObject $object){

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
	protected function updateOneToOneRelationships(DomainObject $entity){

		$oneToOne = NodeReflector::getOneToOneAssociationsAsValues($entity);
		
		foreach ($oneToOne as $relationshipType => $object){

			// DELETE relationship
			$this->deleteRelationship($entity, $relationshipType);

			// INSERT relationship 
			$this->mergeRelationship($entity, $object, $relationshipType);

		}
		
	}

	protected function updateOneToManyRelationships(DomainObject $entity){
		
		$oneToMany = NodeReflector::getOneToManyAssociationsAsValues($entity);

		foreach ($oneToMany as $relationshipType => $collection) {
			
			if( $collection->isEmpty() ){
				continue;
			}
			
			foreach ($collection->getRemovedObjects() as $object) {
				// DELETE relationship
				$this->deleteRelationship($entity, $relationshipType, $object);
			}

			foreach ($collection->get() as $object) {
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
	protected function deleteRelationship(DomainObject $from, $type, DomainObject $to = null){

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

}