<?php 
namespace QNetwork\Infrastructure\OGM\Core;
use QNetwork\Infrastructure\OGM\Core\OGMException;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * @author Cezar Grigore 
 * 
 * This is the uuid that represents the identity of an entity
 */
class Id {

	protected $id = null;

	public function __construct($id = null){
		
		if($id == null){
			
			try {
				$this->id = Uuid::uuid4()->toString();
			}catch(UnsatisfiedDependencyException $e){
				throw new OGMException('There has been a problem with creating your uuid.');
			}

		}else{

			if( ! is_string($id) || strlen($id) < 32){

				throw new InvalidIdException;

			}

			$this->id = $id;

		}

	}

	public function __toString(){

		return (string) $this->id;
		
	}

}