<?php 
namespace Models;
use Annotations\Id;

abstract class Entity{

	/**
	 * @Id
	 * 
	 * @var Core\Id
	 */
	protected $id = null;	

	public function getId(){

		return $this->id;

	}

}