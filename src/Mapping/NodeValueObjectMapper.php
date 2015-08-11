<?php 
namespace Mapping;
use Meta\NodeValueObject as NodeValueObjectMeta;

/**
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
class NodeValueObjectMapper extends NodeMapper{

	protected function load($properties){
		
		return $this->doLoad( $properties );
		
	}

	public function insert(NodeValueObjectMeta $object){

		$statement = $this->getStatementForMerge($object);
		$this->addNodeStatement($statement[0], $statement[1]);
		// dd('ola');
	}

	/**
	 * 
	 * @return [query, params]
	 */
	public function getStatementForMerge(NodeValueObjectMeta $object){

		$class = $object->getClass();
		
		/**
		 * get labels and the node properties as values defined by metadata;
		 * the node properties are the properties on the object with Annotations\GraphProperty
		 */
		$labels = $object->getLabels();
		$labels = $this->mapLabelsToCypher($labels);
		
		
		$matchProperties = $object->getMatchProperties();
		$properties = array_merge($object->getProperties(), 
			[ 'created_at' => 'Some time', '_class' => $class ]);
		$params = $properties;
		
		$matchProperties = $this->mapPropertiesToCypherForMatch($matchProperties);
		$properties = $this->mapPropertiesToCypher($properties);
		
		$query = "MERGE (value:{$labels} {{$matchProperties}}) ON CREATE SET {$properties}";
		
		return [$query, $params];
		
	}

}