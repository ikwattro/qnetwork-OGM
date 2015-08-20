<?php
namespace Models;
use Annotations\Node;
use Annotations\ValueObject as GraphValueObject;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/**
 * @Node(labels={"Domain"})
 * @GraphValueObject
 */
class Domain{

	/**
	 * @GraphProperty(type = "string", key = "domain", match = true)
	 * @var string
	 */
	private $domain = null;

	public function __construct($domain){

		if( ! is_string($domain) || strpos($domain, "@") === false ){
			throw new \Exception('Invalid string for domain.');
		}

		$this->domain = $domain;

	}

	public function __toString(){

		return $this->domain;

	}

	public function equals(Domain $domain){

		return (string) $this === (string) $domain;
		
	}

}