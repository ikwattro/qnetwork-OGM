<?php 
namespace Mapping;
use Meta\NodeEntity as NodeEntityMeta;

/**
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
class NodeEntityMapper extends NodeMapper{

	public function load($properties){
		
		$entity = $this->doLoad( $properties );
		
		$fromMap = $this->getUnitOfWork()->getIdentityMap()->retrieveByEntityId( $entity->getId() );
		if($fromMap){
			return $fromMap;
		}

		$this->getUnitOfWork()->clean($entity);
		return $entity;

	}

	public function insert(NodeEntityMeta $entity){
		
		$statement = $this->getStatementForSettingNodeProperties($entity);
		$this->addNodeStatement($statement[0], $statement[1]);

		$this->updateRelationships($entity);
		
		/**
		 * Clean the entity - add it to identity map
		 * and keep a clone of this original for future updates
		 */
		// $this->getUnitOfWork()->clean($entity);

	}

	public function update($entity){
		
		
	}

}