<?php 
namespace Mapping;
use Core\UnitOfWork;

abstract class AbstractMapper {

	/**
	 * @var Core\UnitOfWork
	 */
	protected $unitOfWork = null;

	public function __construct(UnitOfWork $unitOfWork){

		$this->unitOfWork = $unitOfWork;
		
	}

	public function getUnitOfWork(){

		return $this->unitOfWork;

	}

	/**
	 * Inserts a cypher node statement in the transactional manager
	 * managed by the unit of work assigned to this mapper
	 *
	 * @return void
	 */
	protected function addNodeStatement( $query, $params = [] ){
		
		$this->getUnitOfWork()->getManager()->addNodeStatement([
            'statement' => $query, 
            'parameters' => $params
            ]);

		return $this;

	}

	/**
	 * Inserts a cypher relationship statement in the transactional manager
	 * managed by the unit of work assigned to this mapper
	 *
	 * @return void
	 */
	protected function addRelationshipStatement( $query, $params = [] ){

		$this->getUnitOfWork()->getManager()->addRelationshipStatement([
            'statement' => $query, 
            'parameters' => $params
            ]);

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
	/**
	 * Returning the results set from client
	 *
	 * @param string
	 * @param array
	 * @return ResultSet
	 */
	public function getResults($query, $params){

		$client = Client::create();
		return $client->sendCypherQuery($query, $params)->getResult();
		
	}

}