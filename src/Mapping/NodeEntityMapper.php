<?php 
namespace Mapping;
use Meta\NodeEntity as NodeEntityMeta;
use Core\Uuid;

class NodeEntityMapper extends NodeMapper{

	/*public function load($properties){
		
		$entity = $this->doLoad( $properties );
		
		$fromMap = $this->getUnitOfWork()->getIdentityMap()->retrieveByEntityId( $entity->getId() );
		if($fromMap){
			return $fromMap;
		}

		$this->getUnitOfWork()->clean($entity);
		return $entity;

	}*/

	public function match($object, $name = 'value'){
		
		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		
		// Getting the id of the entity to do the match on
		$key = $meta->getId()->propertyName;
		$id = (string) $meta->getId($object);

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();

		// Start mapping to cypher
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);

		list($cypherMatch, $params) = $this->mapPropertiesArrayToCypherAndToUniqueParamsForMatch([$key => $id]);
		$query = "MATCH ($name:{$labelsCypher} { {$cypherMatch} })";
		
		return [$query, $params];

	}

	public function insert($object){
		
		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		$class = $meta->getClass();

		// Generate Uuid and set it on the property that has @Id annotation
		$id = $meta->getId($object);
		$key = $meta->getId()->propertyName;

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();
		// Gets properties for the node, (key, value) and also attach _class and created_at
		$properties = $this->getNodeProperties($object);
		$properties = array_merge($properties, [ $key => (string) $id, 'created_at' => 'todo', '_class' => $class ]);

		// Start mapping to cypher
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);
		$propertiesCypher = $this->mapPropertiesArrayToCypher($properties);

		// building the query and sending the statement
		$query = "CREATE (value:{$labelsCypher}) SET {$propertiesCypher}";
		$this->addNodeStatement($query, $properties);

		$this->mergeAllRelationships($object);
		
		/**
		 * Clean the entity - add it to identity map
		 * and keep a clone of this original for future updates
		 */
		// $this->getUnitOfWork()->clean($entity);

	}

	public function update($entity){
		// TODO
		/**
		 * Define params as merged properties and created date / update date
		 */
		switch( $object->getState() ){
			case ObjectState::STATE_NEW:
			$params = [ 'created_at' => 'todo', '_class' => $class ];
			$params = array_merge($properties, $params);
			$properties = $this->mapPropertiesToCypher($params);

			$query = "CREATE (value:{$labels}) SET {$properties}";
			break;

			case ObjectState::STATE_DIRTY;
			$params = [ 'updated_at' => 'todo' ];
			$params = array_merge($properties, $params);
			$properties = $this->mapPropertiesToCypher($params);

			$query = "MATCH (value:{$labels} { id: {id} }) SET {$properties}";
			break;

			default:
			throw new OGMException('You are trying to set the node properties but the state provided is invalid.');
			break;
		}

		return [$query, $params];
		
	}

}