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
 * @Node(labels={"EmailAddress"})
 * @GraphValueObject
 *
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class EmailAddress{

	/**
	 * @GraphProperty(type = "string", key = "email", match = true)
	 * @Match
	 */
	private $email = null;
	
	/**
	 * @RelateTo(type = "ON_DOMAIN")
	 */
	private $domain = null;

	public function __construct($email){

		$this->disallowInvalidEmailAddress($email);
		$this->email = $email;
		$this->domain = new Domain(strstr($email, "@"));

	}

	private function disallowInvalidEmailAddress($email){

		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) ){
			throw new Exception('Invalid string for email address.');;
		}

	}

	public function getDomain(){

		return $this->domain;
		
	}

	public function __toString(){
		
		return $this->email;

	}

	public function equals(EmailAddress $email){

		return (string) $this === (string) $email;

	}

}