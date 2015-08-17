<?php 
namespace Mapping;

class NodeValueObjectMapper extends NodeMapper{

	public function match($object, $name = 'value'){
		
		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		
		// Getting the match properties and map the annotations and values to a [key, property] array
		$annotations = $meta->getMatchProperties();
		$values = $meta->getMatchProperties($object);
		$matchProperties = [];
		foreach ($values as $propertyName => $value) {
			$matchProperties[$annotations[$propertyName]->key] = $value;
			settype($matchProperties[$annotations[$propertyName]->key], $annotations[$propertyName]->type);
		}

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();

		// Start mapping to cypher
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);
		list($cypherMatch, $params) = $this->mapPropertiesArrayToCypherAndToUniqueParamsForMatch($matchProperties);
		$query = "MATCH ($name:{$labelsCypher} { {$cypherMatch} })";
		
		return [$query, $params];

	}

	public function insert($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		$class = $meta->getClass();

		// Gets the labels defined by metadata
		$labels = $meta->getLabels();
		
		// Gets properties for the node, (key, value) and also attach _class and created_at
		$properties = $this->getNodeProperties($object);
		$properties = array_merge($properties, [ 'created_at' => 'todo', '_class' => $class ]);

		// Getting the match properties and map the annotations and values to a [key, property] array
		$annotations = $meta->getMatchProperties();
		$values = $meta->getMatchProperties($object);
		$matchProperties = [];
		foreach ($values as $propertyName => $value) {
			$matchProperties[$annotations[$propertyName]->key] = $value;
			settype($matchProperties[$annotations[$propertyName]->key], $annotations[$propertyName]->type);
		}

		// Start mapping to cypher
		$labelsCypher = $this->mapLabelsArrayToCypher($labels);
		$propertiesCypher = $this->mapPropertiesArrayToCypher($properties);
		$matchPropertiesCypher = $this->mapPropertiesArrayToCypherForMatch($matchProperties);

		// building the query
		$query = "MERGE (value:{$labelsCypher} {{$matchPropertiesCypher}}) ON CREATE SET {$propertiesCypher}";
		$this->addNodeStatement($query, $properties);

		$this->mergeAllRelationships($object);

	}

}