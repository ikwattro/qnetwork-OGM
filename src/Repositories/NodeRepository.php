<?php 
namespace QNetwork\Infrastructure\OGM\Repositories;
use QNetwork\Infrastructure\OGM\Reflection\NodeReflector;
use QNetwork\Infrastructure\OGM\Core\UnitOfWork;

class NodeRepository extends AbstractRepository{

	public function __construct(UnitOfWork $unitOfWork, $class){

		parent::__construct($unitOfWork, $class);

	}

	protected function getLabelsQuery(){

		$labels = NodeReflector::getLabels($this->class);

		$class = $this->class;
		$mapper = $class::getMapper();
		$query = $mapper->mapLabelsToCypher($labels);

		return $query;

	}

	/**
	 * This method is applicable only to entities
	 */
	public function findById($id){

		$params = [ 'id' => (string) $id ];
		$labels = $this->getLabelsQuery();
		$query = "MATCH (u:{$labels} { id: {id} }) RETURN u";

		$class = $this->class;
		$mapper = $class::getMapper();

		return $mapper->getSingle($query, $params);

	}

	public function findByProperty($key, $value){

		$labels = $this->getLabelsQuery();

		$params = [ 'paramKey' => $value ];
		$query = "MATCH (value:{$labels} { {$key}: {paramKey} }) RETURN value";
		
		$class = $this->class;
		$mapper = $class::getMapper();
		
		$object = $mapper->findSingle($query, $params);
		return $object;

	}

}