<?php 
namespace QNetwork\Infrastructure\OGM\Repositories;
use QNetwork\Infrastructure\OGM\Reflection\NodeReflector;
use QNetwork\Infrastructure\OGM\Core\UnitOfWork;

abstract class NodeRepository extends AbstractRepository{

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

	public function getByProperty($key, $value){

		$labels = $this->getLabelsQuery();

		$params = [ 'paramKey' => $value ];
		$query = "MATCH (value:{$labels} { {$key}: {paramKey} }) RETURN value";
		
		$class = $this->class;
		$mapper = $class::getMapper();
		
		$object = $mapper->findSingle($query, $params);
		return $object;

	}

}