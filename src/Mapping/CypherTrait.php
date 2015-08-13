<?php 
namespace Mapping;

trait CypherTrait{

	/**
	 * Transform an array of labels to cypher
	 *
	 * @param array
	 * @return string
	 */
	public function mapLabelsToCypher($labels){

		$cypherLabels = '';
		foreach ($labels as $label) {
			
			$cypherLabels .= $label . ':';

		}

		$cypherLabels = rtrim($cypherLabels, ":");

		return $cypherLabels;

	}

	protected function mapPropertiesToCypher($properties, $name = 'value'){

		$cypher = '';

		foreach ($properties as $property => $value) {
			
			$cypher .= "$name.$property={" . $property ."},";

		}
		
		return rtrim($cypher, ",");

	}

	protected function mapPropertiesToCypherForMatch($properties, $name = 'value'){

		$cypher = '';

		foreach ($properties as $property => $value) {
			
			$cypher .= "$property: {" . $property ."},";

		}
		
		return rtrim($cypher, ",");

	}
	
}