<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class TransactionalManager{

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

		echo 'transactional manager commit';
		die();

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