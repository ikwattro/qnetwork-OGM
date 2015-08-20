<?php
namespace Tests\OGM\Core;
use Tests\OGMTest;
use Core\Object;
use Models\EmailAddress;
use Models\Password;
use Models\User;

class ProxyFactoryTest extends OGMTest{

	public function testProxyGenerateFromDomainObject(){
		
		$rep = $this->unitOfWork->getRepository(User::class);
		$user = $rep->findByProperty('username', 'j.smith@mydomain.io');

	}

}