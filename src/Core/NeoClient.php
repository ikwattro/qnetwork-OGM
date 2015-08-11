<?php 
namespace Core;

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

	public function commit(){

		$statements = array_merge($this->getNodeStatements(), $this->getRelationshipStatements());
		
		$client = Client::create();
	
		try {

			$client->sendMultiple($statements);

		}catch(\Exception $e){
			
			throw $e;
			
		}

		$this->nodeStatements = [];
		$this->relationshipStatements = [];

	}

	public function clear(){

		echo 'transactional manager clear';
		die();
		
	}

	public function addNodeStatement($statement){

		if( ! isset($statement['statement']) || ! is_string($statement['statement'])){
			throw new BadStatementException;
		}

		if( ! isset($statement['parameters']) || ! is_array($statement['parameters'])){
			throw new BadStatementException;
		}

		$this->nodeStatements[] = $statement;
		
	}

	public function addRelationshipStatement($statement){

		if( ! isset($statement['statement']) || ! is_string($statement['statement'])){
			throw new BadStatementException;
		}

		if( ! isset($statement['parameters']) || ! is_array($statement['parameters'])){
			throw new BadStatementException;
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