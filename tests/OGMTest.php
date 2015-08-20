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
use Core\Collection;

class OGMTest extends \PHPUnit_Framework_TestCase {

	public function setUp(){

		\Kint::enabled(true);

		$this->faker = \Faker\Factory::create();
		
		$reflector = new ReflectionClass(new AnnotationReader(), new AnnotationRegistry());
		$this->unitOfWork = new UnitOfWork(
			new NeoClient(), new MetadataFactory($reflector)
			);

	}

	public function testCase1(){
		
		// TODO: NOT WORKING !!!
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

	public function testCase3(){

		$rep = $this->unitOfWork->getRepository(User::class);
		$user = $rep->findById('4e1a71b8-c1eb-432f-9173-2554c446926c');

		$email = (string) $user->getUsername()->getDomain();
		$person = $user->getPerson();
		echo $person->getOrganization()->getName();

	}

	public function testCase4(){

		$rep = $this->unitOfWork->getRepository(User::class);
		$user = $rep->findByProperty('username', 'j.smith@mydomain.io');
		$person = $user->getPerson();
		$person->getName();
		$username = (string) $user->getUsername();

		$newuser = new User(new EmailAddress('test@domain.com'), new Password('olalala'));
		$this->unitOfWork->persist($newuser);
		$this->unitOfWork->commit();

	}

	public function testCase5(){

		$person = new Person($this->faker->name);
		$this->unitOfWork->persist($person);

		$collection = new Collection();
		for($i = 1; $i <= 100; $i++) {
			
			$email = new EmailAddress($this->faker->email);
			// $collection->add($email);
			$person->addEmail($email);

		}
		
		$this->unitOfWork->commit();
		
	}

	public function testCase6(){

		$rep = $this->unitOfWork->getRepository(Person::class);
		$person = $rep->findByProperty('name', 'Test 1');
		$person->removeEmail(new EmailAddress('test@domain.com'));
		$emails = $person->getEmails();

		$this->unitOfWork->commit();

	}

	public function testCase7(){

		$rep = $this->unitOfWork->getRepository(User::class);
		$user = $rep->findById('');
		dd($user);
		
	}

}