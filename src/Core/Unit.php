<?php 
namespace Core;

interface Unit{

	public function persist($object);

	public function remove($object);

}