<?php 
namespace QNetwork\Infrastructure\OGM\Core;
use QNetwork\Infrastructure\OGM\Annotations\GraphProperty;

/**
 * We consider an Entity anything that has a thread of continuity and identity; check DDD.
 * The additional behavior that this has compared to ValueObject's 
 * is that it has a uuid.
 *
 * @author Cezar Grigore 
 */
abstract class Entity extends DomainObject {
	
	/**
	 * @GraphProperty(type = "string", key = "id", match = true, reference = "QNetwork\Infrastructure\OGM\Core\Id")
	 *
	 * @var QNetwork\Infrastructure\OGM\Core\Id
	 */
	protected $id = null;

	public function __construct(){

		parent::__construct();

		$this->id = new Id();

	}

	/**
	 * @return QNetwork\Infrastructure\OGM\Core\Id
	 */
	public function getId(){

		return $this->id;

	}

	/**
	 * @return boolean
	 */
	public function isEntity(){

		return true;

	}

}