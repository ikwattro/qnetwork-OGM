<?php 
namespace Core;
use Rhumsaa\Uuid\Uuid as RhumsaaUuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

/** 
 * This is the uuid that represents the identity of an entity
 * It will be auto-generated at INSERT but the user has to have an @Id annotation on the object
 */
class Uuid {

	protected $id = null;

	public function __construct($id = null){
		
		if($id == null){
			
			try {
				$this->id = RhumsaaUuid::uuid4()->toString();
			}catch(UnsatisfiedDependencyException $e){
				throw new OGMException('There has been a problem with creating your uuid.');
			}

		}else{

			$this->id = $id;
			
		}

	}

	public function __toString(){

		return (string) $this->id;
		
	}

}