<?php
namespace Tests;
use Models\User;
use Models\EmailAddress;
use Models\Person;
use Models\Organization;
use Models\Website;
use Models\Password;
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
		$this->unitOfWork = new UnitOfWork(
			new NeoClient(), new MetadataFactory($reflector)
			);

	}

	public function testCase1(){
		
		$person = new Person('John Smith');
		$person->addEmail(new EmailAddress('john.smith@gmail.com'))
			->addEmail(new EmailAddress('j.smith@mydomain.io'));

		$user = new User(new EmailAddress('j.smith@mydomain.io'), new Password('123123'));
		$user->isA($person)->newSession();
		
		$this->unitOfWork->persist($user);
		$this->unitOfWork->commit();

	}

	public function testCase2(){	

		$organization = new Organization('Alphabet');
		$organization->addEmail(new EmailAddress('corporate@abc.xyz'))
			->addWebsite(new Website('abc.xyz'));

		$person = new Person('John Smith');
		$person->addEmail(new EmailAddress('john.smith@gmail.com'))
			->addEmail(new EmailAddress('j.smith@mydomain.io'))
			->addEmail(new EmailAddress('corporate@abc.xyz'))
			->worksFor($organization);

		$user = new User(new EmailAddress('j.smith@mydomain.io'), new Password('123123'));
		$user->isA($person)->newSession();
		
		$this->unitOfWork->persist($user);
		$this->unitOfWork->commit();

	}

	public function testOGM(){

	}

}