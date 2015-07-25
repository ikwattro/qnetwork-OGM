<?php 
namespace QNetwork\Infrastructure\OGM\Mappers;
use QNetwork\Infrastructure\OGM\Core\Entity;
use QNetwork\Infrastructure\OGM\Reflection\NodeReflector;
use QNetwork\Infrastructure\OGM\Core\State;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use QNetwork\Infrastructure\OGM\Core\EntityCollection;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class NodeEntityMapper extends NodeMapper{

	protected function load($results){
		
		$entity = $this->doLoad( $results->getSingleNodeByLabel(current($labels)) );
		
		/**
		 * -> check documentation from clean() function
		 * it returns the clean entity and this one should be used from now on
		 */
		$clean = $this->getUnitOfWork()->clean($entity);

		/** 
		 * If the $clean points to a different object than
		 * $entity then $clean exists in identity map already and 
		 * this is the work object in this session. 
		 * a $unitOfWork->clear() will remove all objects from unit of work
		 * and let you pull the entity again
		 */
		if( $clean !== $entity){
			return $clean;
		}

		return $entity;
		// $labels = NodeReflector::getEagerLoading( get_class($entity) );
		// dd($labels);
		/*for( $results->getNodes() as $node){
			$objects[$node->getProperty('_class')] = ;
		}*/


	}

	public function insert(Entity $entity){

		/**
		 * Setting the node properties for entity
		 */	
		$this->setNodeProperties($entity);
		
		/**
		 * Clean the entity - add it to identity map
		 * and keep a clone of this original for future updates
		 */
		$this->getUnitOfWork()->clean($entity);

	}

	public function update(Entity $entity){
		
		/**
		 * Go through GraphProperties and if any is different than original
		 * then update all graph properties on node
		 */
		$this->updateGraphProperties($entity);

		/**
		 * Clean the entity - add it to identity map
		 * and keep a clone of this original for future updates
		 */
		$this->getUnitOfWork()->clean($entity);
	}

	/**
	 * Loops through all graph properties of $entity and checks
	 * them against the $original object (usually pulled from DB or cleaned).
	 * If any property is different we update all properties on node
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @return void
	 */
	protected function updateGraphProperties(Entity $entity){

		$original = $this->getUnitOfWork()->getOriginalEntity($entity->getId());
		
		if($original === null){
			throw new OGMException('You are trying to update graph properties for an entity 
				that does not have an original object.');
		}

		$entityProperties = NodeReflector::getGraphPropertiesAsValues($entity);
		$originalProperties = NodeReflector::getGraphPropertiesAsValues($original);
		
		foreach ($entityProperties as $key => $value) {
			
			if( $value != $originalProperties[$key] ){

				$this->setNodeProperties($entity, State::DIRTY);
				return;

			}

		}

		return;

	}

	/**
	 * Creates the query that sets the node properties for a specific entity.
	 * If it is a NEW entity then we CREATE the entity.
	 * If it is a DIRTY entity then we just MATCH and SET.
	 *
	 * @param QNetwork\Infrastructure\OGM\Core\Entity
	 * @param QNetwork\Infrastructure\OGM\Core\State
	 * @return void
	 */
	protected function setNodeProperties(Entity $entity, $state = State::__default){

		/**
		 * get labels and node properties defined by metadata
		 */
		$labels = NodeReflector::getLabels($entity);
		$properties = NodeReflector::getGraphPropertiesAsValues($entity);
		
		/**
		 * Define params as merged properties and created date / update date
		 */
		if($state === State::__default){
			$params = [ 'created_at' => $this->getDateTime(), '_class' => $this->class ];
		}elseif($state === State::DIRTY){
			$params = [ 'updated_at' => $this->getDateTime() ];
		}
		
		$params = array_merge($properties, $params);

		/**
		 * Map properties and labels to cypher in order to create the query
		 */
		$properties = $this->mapPropertiesToCypher($params);
		$labels = $this->mapLabelsToCypher($labels);
		
		/**
		 * Create query and add transactional statement
		 * If it is a NEW entity then we use CREATE
		 * If it is a DIRTY entity then we use MATCH
		 */
		if($state === State::__default){
			$query = "CREATE (value:{$labels}) SET {$properties}";
		}elseif($state === State::DIRTY){
			$query = "MATCH (value:{$labels} { id: {id} }) SET {$properties}";
		}else{
			throw new OGMException('You are trying to set the node properties but the state provided is invalid.');
		}

		$this->addNodeStatement($query, $params);

	}

}