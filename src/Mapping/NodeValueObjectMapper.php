<?php 
namespace Mapping;
use Meta\NodeValueObject as NodeValueObjectMeta;

/**
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
class NodeValueObjectMapper extends NodeMapper{

	protected function load($properties){
		
		return $this->doLoad( $properties );
		
	}

	public function insert(NodeValueObjectMeta $object){

		$statement = $this->getStatementForMerge($object);
		dd($statement);

	}

	public function getStatementForMerge(NodeValueObjectMeta $object){

		$class = $object->getClass();
		
		/**
		 * get labels and the node properties as values defined by metadata;
		 * the node properties are the properties on the object with Annotations\GraphProperty
		 */
		$labels = $object->getLabels();
		$labels = $this->mapLabelsToCypher($labels);
		$properties = $object->getProperties();
		

		/**
		 * Loops through $object meta and detects which graph properties
		 * are being used for the match and which ones aren't.
		 * The non match properties will be created at insert.
		 */
		$matchProperties = $object->getMatchProperties();
		dd($matchProperties);
		$nonMatchProperties = array_merge($nonMatchProperties, [ 'created_at' => $this->getDateTime(), '_class' => $class ]);
		
		/**
		 * Define $params that are being passed with the statemtn as 
		 * the match properties merged with the nonMatchProperties
		 */
		$params = array_merge($matchProperties, $nonMatchProperties);

		/**
		 * Map match properties, non match properties and labels to cypher 
		 * in order to create the query.
		 */
		$matchProperties = $this->mapPropertiesToCypherForMatch($matchProperties);
		$nonMatchProperties = $this->mapPropertiesToCypher($nonMatchProperties);
		$labels = $this->mapLabelsToCypher($labels);
		
		/**
		 * Building the transactional query and adding it to unit of work -> transactional manager
		 */
		$query = "MERGE (value:{$labels} {{$matchProperties}}) ON CREATE SET {$nonMatchProperties}";
		$this->addNodeStatement($query, $params);
		
		/**
		 * maps the relationships for this value object
		 */
		// $this->insertOneToOneRelationships($object);
		// $this->insertOneToManyRelationships($object);

	}

}