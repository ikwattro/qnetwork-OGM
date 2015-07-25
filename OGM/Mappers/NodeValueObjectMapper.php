<?php 
namespace QNetwork\Infrastructure\OGM\Mappers;
use QNetwork\Infrastructure\OGM\Core\ValueObject;
use QNetwork\Common\DateTime;
use QNetwork\Infrastructure\OGM\Reflection\NodeReflector;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class NodeValueObjectMapper extends NodeMapper{

	protected function load($results){

		/**
		 * If the result set is empty, return null
		 */
		if( ! count($results->getNodes()) ){
			return null;
		}
		
		return $this->doLoad( $results->getSingleNode() );
		
	}

	public function merge(ValueObject $object){

		/**
		 * get labels and node properties defined by metadata
		 */
		$labels = NodeReflector::getLabels($object);
		$properties = NodeReflector::getGraphPropertiesAsValues($object);
		

		/**
		 * Loops through $object meta and detects which graph properties
		 * are being used for the match and which ones aren't.
		 * The non match properties will be created at insert.
		 */
		$matchProperties = NodeReflector::getMatchPropertiesAsValues($object);
		$nonMatchProperties = NodeReflector::getNonMatchPropertiesAsValues($object);

		$nonMatchProperties = array_merge($nonMatchProperties, [ 'created_at' => $this->getDateTime(), '_class' => $this->class ]);
		
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