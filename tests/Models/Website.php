<?php
namespace Models;
use Annotations\Node;
use Annotations\ValueObject as GraphValueObject;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\Match;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/**
 * @Node(labels={"Website"})
 * @GraphValueObject
 */
class Website{

	/**
	 * @GraphProperty(type = "string", key = "url", match = true)
	 */
	private $url = null;
	
	/**
	 * @RelateTo(type = "ON_DOMAIN")
	 */
	private $domain = null;

	public function __construct($url){

		$this->url = $url;
		$domain = '@' . $url;
		$this->domain = new Domain($domain);

	}

	public function getDomain(){

		return $this->domain;
		
	}

	public function __toString(){
		
		return $this->url;

	}

	public function equals(Website $website){

		return (string) $this === (string) $website;

	}

}