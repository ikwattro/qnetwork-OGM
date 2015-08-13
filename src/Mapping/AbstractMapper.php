<?php 
namespace Mapping;
use Core\UnitOfWork;

/**
 * @author Cezar Grigore <tuck2226@gmail.com>
 */
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