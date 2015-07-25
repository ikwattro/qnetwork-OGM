<?php 
namespace QNetwork\Infrastructure\OGM\Mappers;
use QNetwork\Infrastructure\OGM\Core\UnitOfWork;
use QNetwork\Infrastructure\OGM\Core\Client;

abstract class AbstractMapper {

	/**
	 * @var QNetwork\Infrastructure\OGM\Core\UnitOfWork
	 */
	protected $unitOfWork = null;

	/**
	 * The class name that this repository is being created for.
	 * eg. QNetwork\Domain\User, QNetwork\Domain\EmailAddress
	 *
	 * @var string
	 */
	protected $class;

	public function __construct(UnitOfWork $unitOfWork, $class){

		$this->unitOfWork = $unitOfWork;
		$this->class = $class;
		
	}

	public function getUnitOfWork(){

		return $this->unitOfWork;

	}

	public function getDateTime(){

		$time = new \DateTime();
		return (string) $time->format('Y-m-d H:i:s');

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
	 * Sends a request to DB with ($query, $params) and returns $nodes
	 *
	 * @param string
	 * @param array
	 * @param array
	 */
	protected function getNodesFromQuery($query, $params, $nodes = null){

		$client = Client::create();
		return $client->sendCypherQuery($query, $params)->getResult();

	}

}