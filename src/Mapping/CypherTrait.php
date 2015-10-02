<?php 
namespace Mapping;

trait CypherTrait{

	/**
	 * Transform an array of labels to cypher
	 *
	 * @param array
	 * @return string
	 */
	public function mapLabelsArrayToCypher($labels){

		$cypherLabels = '';
		foreach ($labels as $label) {
			$cypherLabels .= $label . ':';
		}

		$cypherLabels = rtrim($cypherLabels, ":");
		return $cypherLabels;

	}

	/**
	 * Transform an array of labels to cypher
	 *
	 * @param array The properties array
	 * @param string The matching name
	 * @return string The cypher query
	 */
	protected function mapPropertiesArrayToCypher($properties, $name = 'value'){

		$cypher = '';
		foreach ($properties as $property => $value) {
			$cypher .= "$name.$property={" . $property ."},";
		}
		
		return rtrim($cypher, ",");

	}

	/**
	 * Transform an array of labels to cypher for match
	 *
	 * @param array The properties array
	 * @param string The matching name
	 * @return string The cypher query
	 */
	protected function mapPropertiesArrayToCypherForMatch($properties, $name = 'value'){

		$cypher = '';
		foreach ($properties as $property => $value) {
			$cypher .= "$property: {" . $property ."},";
		}
		
		return rtrim($cypher, ",");

	}
	
	public function mapPropertiesArrayToCypherAndToUniqueParamsForMatch($properties){

		$cypher = '';
		$newParams = [];

		foreach ($properties as $property => $value) {
			
			$key = uniqid() . '_' . $property; 
			$newParams[$key] = $value;

			$cypher .= "$property: {" . $key ."},";

		}
		
		$cypher = rtrim($cypher, ",");
		return [$cypher, $newParams];

	}
}