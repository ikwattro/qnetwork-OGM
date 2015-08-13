<?php 
namespace Mapping;

trait NodeFinder {
	/**
	 * This mapping function returns a single domain object
	 * that is represented by a node in the database.
	 * All the domain objects attached to this one will be represented as proxies
	 *
	 * @param string The query that will be run
	 * @param params The params that are being passed to the query
	 * @return DomainObject
	 */
	public function getSingle($query, $params = []){

		$results = $this->getResults($query, $params);
		
		/**
		 * If the result set is empty, return null
		 */
		if( ! count($results->getNodes()) ){
			return null;
		}

		if( count($results->getNodes()) > 1){
			throw new OGMException('The provided query is invalid. query: ' . $query);
		}

		return $this->load( $results->getSingleNode()->getProperties() );

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
}
