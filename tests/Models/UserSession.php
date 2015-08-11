<?php
namespace Models;
use Annotations\Node;
use Annotations\Entity as GraphEntity;
use Annotations\Repository;
use Annotations\Mapper;
use Annotations\RelateTo;
use Annotations\GraphProperty;

/** 
 * @Node(labels = {"UserSession"})
 * @GraphEntity
 * 
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class UserSession {

	/**
	 * @GraphProperty(type = "string", key = "started_at")
	 *
	 * @var string
     */
	protected $startedAt = null; 

	public function __construct(){

		$this->startedAt = 'Some time';

	}

}
