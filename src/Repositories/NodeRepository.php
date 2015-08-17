<?php 
namespace Repositories;
use Meta\NodeEntity as MetaNodeEntity;
use Core\OGMException;

class NodeRepository extends AbstractRepository{

	/**
	 * This method is applicable only to entities
	 */
	public function findById($id){

		$meta = $this->getMeta();
		$class = $meta->getClass();

		if( ! $meta instanceof MetaNodeEntity ){
			throw new OGMException('You cannot get by id for something that is not an entity.');
		}

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);

		$params = [ 'id' => (string) $id ];
		$query = "MATCH (node:{$labelsCypher} { id: {id} }) RETURN node";
		
		return $this->getUnitOfWork()->getNodeFinder($class)->getSingle($query, $params);

	}

	public function findByProperty($key, $value){

		$meta = $this->getMeta();
		$class = $meta->getClass();
		
		// Gets the labels defined by metadata
		$labels = $meta->getLabels();
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);

		$params = [ 'paramKey' => $value ];
		$query = "MATCH (value:{$labelsCypher} { {$key}: {paramKey} }) RETURN value";
		
		return $this->getUnitOfWork()->getNodeFinder($class)->getSingle($query, $params);

	}

}