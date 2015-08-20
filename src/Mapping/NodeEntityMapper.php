<?php 
namespace Mapping;
use Core\OGMException;
use Meta\NodeEntity as NodeEntityMeta;
use Core\Uuid;

class NodeEntityMapper extends NodeMapper{
	
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
		 * and (keep a clone of this original for future updates?)
		 */
		$this->getUnitOfWork()->clean($object);

	}

	public function update($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		$class = $meta->getClass();

		// Generate Uuid and set it on the property that has @Id annotation
		$id = $meta->getId($object);
		$key = $meta->getId()->propertyName;

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();
		// Gets properties for the node, (key, value) and also attach _class and created_at
		$properties = $this->getNodeProperties($object);
		$properties = array_merge($properties, [ $key => (string) $id, 'updated_at' => 'todo', '_class' => $class ]);

		// Start mapping to cypher
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);
		$propertiesCypher = $this->mapPropertiesArrayToCypher($properties);

		// building the query and sending the statement
		$query = "MATCH (value:{$labelsCypher} {id: {{$key}} }) SET {$propertiesCypher}";
		$this->addNodeStatement($query, $properties);

		$this->mergeAllRelationships($object);
		
		/**
		 * Clean the entity - add it to identity map
		 * and (keep a clone of this original for future updates?)
		 */
		$this->getUnitOfWork()->clean($object);
		
	}

}