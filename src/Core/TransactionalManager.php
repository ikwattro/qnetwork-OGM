<?php 
namespace Core;

interface TransactionalManager{

	public function getResultSet($query, $params);
	
}