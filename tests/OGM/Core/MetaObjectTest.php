<?php
namespace Tests\OGM\Core;
use Tests\OGMTest;
use Core\Object;
use Models\EmailAddress;
use Models\Password;
use Models\User;

class ObjectTest extends OGMTest{

	public function testCheckIsEntity(){
		
		$user = new User(new EmailAddress('test@domain.com'), new Password('123123'));
		$object = new Object($user);

		$this->assertTrue( $object->isEntity() );

	}

	public function testCheckIsValueObject(){

		$email = new EmailAddress('test@domain.com');
		$object = new Object($email);

		$this->assertFalse( $object->isEntity() );

	}

	/**
	 * @expectedException Core\OGMException
	 */
	public function testCheckIsNeitherEntityOrValueObject(){

		$password = new Password('123123');
		$object = new Object($password);

	}

}