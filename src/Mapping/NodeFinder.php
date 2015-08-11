<?php 
namespace Mapping;

class NodeFinder {
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
}
