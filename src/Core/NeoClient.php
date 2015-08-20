<?php 
namespace Core;
use Neoxygen\NeoClient\ClientBuilder;

class NeoClient implements TransactionalManager{

	
	protected $client = null;

	/**
	 * An array with all the node statements to be pushed in a transaction to DB
	 * 
	 * @var array
	 */
	protected $nodeStatements = [];

	/**
	 * An array with all the relationship statements to be pushed in a transaction to DB
	 * 
	 * @var array
	 */
	protected $relationshipStatements = [];

	public function __construct(){
		
		$this->client = ClientBuilder::create()
				->addConnection('default','http','localhost', 7474, true, 'neo4j', '123123')
				->setDefaultTimeout(180)
				->setAutoFormatResponse(true)
				->build();

	}

	public function flush(){

		$statements = array_merge($this->getNodeStatements(), $this->getRelationshipStatements());
		$client = $this->client;
		try {
			$client->sendMultiple($statements);
		}catch(\Exception $e){
			throw $e;
		}

		$this->nodeStatements = [];
		$this->relationshipStatements = [];

	}

	public function getResultSet($query, $params){
		
		return $this->client->sendCypherQuery($query, $params)->getResult();

	}

	public function clear(){

		echo 'transactional manager clear';
		die();
		
	}

	public function addNodeStatement($statement){

		if( ! isset($statement['statement']) || ! is_string($statement['statement'])){
			throw new WrongStatementException;
		}

		if( ! isset($statement['parameters']) || ! is_array($statement['parameters'])){
			throw new WrongStatementException;
		}

		$this->nodeStatements[] = $statement;
		
	}

	public function addRelationshipStatement($statement){

		if( ! isset($statement['statement']) || ! is_string($statement['statement'])){
			throw new WrongStatementException;
		}

		if( ! isset($statement['parameters']) || ! is_array($statement['parameters'])){
			throw new WrongStatementException;
		}

		$this->relationshipStatements[] = $statement;

	}

	protected function getNodeStatements(){

		return $this->nodeStatements;

	}

	protected function getRelationshipStatements(){

		return $this->relationshipStatements;

	}
}