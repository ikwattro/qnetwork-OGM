<?php
namespace Models;
use Annotations\Node;
use Annotations\Entity as GraphEntity;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/** 
 * @Node(labels = {"Organization"})
 * @GraphEntity
 */
class Organization extends Entity{

	/**
	 * @RelateTo(type = "HAS_EMAIL", collection = true)
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
	 * @RelateTo(type = "HAS_WEBSITE", collection = true)
	 *
	 * @var Models\Website
	 */
	protected $websites = [];

	public function __construct($name){

		$this->name = $name;

	}

	public function addEmail(EmailAddress $email){

		$this->emails[] = $email;
		return $this;

	}

	public function addWebsite(Website $website){

		$this->websites[] = $website;
		return $this;

	}

}
