<?php

namespace Models;

use Annotations\Id;

abstract class Entity{

	/**
	 * @Id
	 * 
	 * @var \Core\Id
	 */
	protected $id = null;

    /**
     * @return \Core\Id
     */
	public function getId(){

		return $this->id;

	}
}