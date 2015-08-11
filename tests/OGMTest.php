<?php
namespace Tests;
use Models\User;
use Models\EmailAddress;
use Core\Object;
use Core\UnitOfWork;
use Core\NeoClient;
use Reflection\Reflector;

class OGMTest extends \PHPUnit_Framework_TestCase {

	public function setUp(){

		\Kint::enabled(true);
		Reflector::registerAnnotations();
		$this->unitOfWork = new UnitOfWork(new NeoClient);

	}

	public function test(){
		
		$email = new EmailAddress('test@domain.com');
		$password = '123123';

		$user = new User($email, $password);
		$proxy = new Object($user);
		
		$this->unitOfWork->persist($user);

	}

	public function testOGM(){

	}

}