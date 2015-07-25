<?php 
namespace QNetwork\Infrastructure\OGM\Repositories;
use QNetwork\Infrastructure\OGM\Core\Id;

class NodeEntityRepository extends NodeRepository{

	public function getById(Id $id){

		$params = [ 'id' => (string) $id ];
		$query = 'MATCH (u:User { id: {id} }) RETURN u';

		$entity = $this->getMapper()->getSingle();

	}

}