<?php 
namespace Tests\OGM\Core;
use Tests\OGMTest;
use Core\Object;
use Models\EmailAddress;
use Models\Password;
use Models\User;
use Proxy\ValueHolder;
use Core\Collection;

class UnitOfWorkTest extends OGMTest{

	public function testPersist(){

		$email = new EmailAddress('test@domain.com');
		$user = new User( $email, new Password('123123') );
		$user->newSession()->newSession()->newSession();

		// $this->unitOfWork->persist( $email->getDomain() );
		$this->unitOfWork->persist( $user );
		$this->unitOfWork
					->persist($user)
					->persist( new EmailAddress('abc@yo.io') )
					->persist( new EmailAddress('lola@up.org') )
					->persist( new EmailAddress('hello@us.gov') );
					
		$this->unitOfWork->commit();

	}

	public function testIsManaged(){

		$email = new EmailAddress('test@domain.com');

		$this->unitOfWork->persist($email);
		$isManaged = $this->unitOfWork->isManaged($email);

		$this->assertTrue($isManaged);

	}

	public function testIsManagedInCascade(){

		$email = new EmailAddress('test@domain.com');
		$user = new User( $email, new Password('123123') );
		$domain = $user->getUsername()->getDomain();
		
		$this->unitOfWork->persist($user);
		$isManaged = $this->unitOfWork->isManaged($domain);

		$this->assertTrue($isManaged);

	}

}
