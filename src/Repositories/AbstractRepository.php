<?php 
namespace Repositories;
use Core\UnitOfWork;
use Mapping\CypherTrait;

abstract class AbstractRepository {

	use CypherTrait;

	/**
	 * @var Core\UnitOfWork
	 */
	protected $unitOfWork = null;

	/**
	 * The meta class for this repository, extracted from the class that has been passed
	 */
	protected $meta = null;

	public function __construct(UnitOfWork $unitOfWork, $class){

		$this->unitOfWork = $unitOfWork;
		$this->meta = $this->unitOfWork->getClassMetadata($class);

	}

	public function getUnitOfWork(){

		return $this->unitOfWork;

	}

	public function getMeta(){

		return $this->meta;
		
	}

}