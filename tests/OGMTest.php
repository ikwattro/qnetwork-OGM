<?php
namespace Tests;
use Models\User;
use Models\EmailAddress;
use Core\Object;
use Core\UnitOfWork;
use Core\UnitOfWork2;
use Meta\MetadataFactory;
use Core\NeoClient;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Meta\ReflectionClass;

class OGMTest extends \PHPUnit_Framework_TestCase {

	public function setUp(){

		\Kint::enabled(true);
		
		$reflector = new ReflectionClass(new AnnotationReader(), new AnnotationRegistry());
		$this->unitOfWork = new UnitOfWork2(
			new NeoClient(), new MetadataFactory($reflector)
			);

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