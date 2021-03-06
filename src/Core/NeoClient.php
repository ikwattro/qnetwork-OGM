<?php 
namespace Core;
use Neoxygen\NeoClient\ClientBuilder;

class NeoClient implements TransactionalManager{

    /**
     * @var \Neoxygen\NeoClient\Client
     */
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

	/**
	 * Credentials should contain: 
	 * 'connection', 'address', 'port', 'username', 'password'
	 *  @var int 'timeout' with default 60 seconds
	 */
	public function __construct($credentials, $timeout = 60){
		
		$this->client = ClientBuilder::create()
				->addConnection('default', 
					$credentials['connection'],
					$credentials['host'], 
					$credentials['port'], 
					true, 
					$credentials['username'], 
					$credentials['password'])
				->setDefaultTimeout($timeout)
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

		$this->nodeStatements = [];
		$this->relationshipStatements = [];

		return ;
		
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

    /**
     * @return \Neoxygen\NeoClient\Client
     */
	public function getClient()
    {
        return $this->client;
    }

	protected function getNodeStatements(){

		return $this->nodeStatements;

	}

	protected function getRelationshipStatements(){

		return $this->relationshipStatements;

	}
}