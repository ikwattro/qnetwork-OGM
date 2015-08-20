<?php
namespace Models;
use Annotations\Node;
use Annotations\Entity as GraphEntity;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/** 
 * @Node(labels = {"Person"})
 * @GraphEntity
 */
class Person extends Entity{

	/**
	 * @RelateTo(type = "HAS_EMAIL", collection = true, reference = "Models\EmailAddress")
	 *
	 * @var Models\EmailAddress
	 */
	protected $emails = [];

	/**
	 * @GraphProperty(type = "string", key = "name")
	 *
	 * @var string
     */
	protected $name = null; 

	/**
	 * @RelateTo(type = "WORKS_FOR", reference = "Models\Organization")
	 *
	 * @var Models\Organization
	 */
	protected $organization = null;

	public function __construct($name){

		$this->name = $name;

	}

	public function setName($name){
		
		$this->name = $name;
		return $this;

	}

	public function getName(){

		return $this->name;
		
	}
	
	public function addEmail(EmailAddress $email){

		$this->emails[] = $email;
		return $this;

	}

	public function getEmails(){

		return $this->emails;
		
	}

	public function worksFor(Organization $organization){

		$this->organization = $organization;
		return $this;
		
	}

	public function getOrganization(){

		return $this->organization;
		
	}

}
