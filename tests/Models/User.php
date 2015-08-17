<?php
namespace Models;
use Annotations\Node;
use Annotations\Entity as GraphEntity;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/** 
 * @Node(labels = {"User"})
 * @GraphEntity
 */
class User extends Entity{
	
	/**
	 * @RelateTo(type = "HAS_USERNAME")
	 * @GraphProperty(type = "string", key = "username")
	 * @var Models\EmailAddress
	 */
	protected $username = null;

	/**
	 * @RelateTo(type = "HAS_SESSION", collection = true)
	 *
	 * @var Models\UserSession
	 */
	protected $sessions = [];

	/**
	 * @GraphProperty(type = "string", key = "password")
	 *
	 * @var string
     */
	protected $password = null; 

	/**
	 * @RelateTo(type = "IS_A")
	 *
	 * @var Models\Person
	 */
	protected $person = null;

	public function __construct(EmailAddress $username, $password){

		$this->username = $username;
		$this->password = $password;

	}

	public function getUsername(){

		return $this->username;

	}

	public function getPassword(){

		return $this->password;
		
	}

	public function newSession(){

		$this->sessions[] = new UserSession();
		return $this;

	}

	public function isA(Person $person){

		$this->person = $person;
		return $this;

	}

	public function getPerson(){

		return $this->person;
		
	}
	
}
