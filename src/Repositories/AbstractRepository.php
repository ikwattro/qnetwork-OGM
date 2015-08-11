<?php 
namespace QNetwork\Infrastructure\OGM\Repositories;
use QNetwork\Infrastructure\OGM\Core\UnitOfWork;
use QNetwork\Infrastructure\OGM\Reflection\Reflector;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
abstract class AbstractRepository {

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

	public function add($object){

		// TODO: check if the object passed has all the required annotations
		$this->getUnitOfWork()->persist($object);

		return $this;

	}

	public function remove($object){

		// TODO: check if the object passed has all the required annotations
		$this->getUnitOfWork()->remove($object);

		return $this;

	}


}