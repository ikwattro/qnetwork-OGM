<?php 
namespace QNetwork\Infrastructure\OGM\Mappers;
use QNetwork\Infrastructure\OGM\Core\DomainObjectCollection;

class DomainObjectCollectionMapper extends AbstractMapper{

	public function __construct(){}

	public function find($query, $params = []){

		$results = $this->getNodesFromQuery($query, $params);
		$collection = new DomainObjectCollection();

		/**
		 * If the result set is empty, return null
		 */
		if( ! count($results->getNodes()) ){
			return $collection;
		}
		dd($results->getTableFormat());
		foreach ($results->getNodes() as $node) {
			
			$class = $node->getProperty('_class');
			if( ! $class ){
				continue;
			}

			$mapper = $class::getMapper();
			$collection->add( $mapper->doLoad($node) );

		}

		$collection->setLoaded();
		return $collection;

	}

}