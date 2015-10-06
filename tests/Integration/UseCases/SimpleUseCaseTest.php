<?php

namespace Tests\Integration\UseCases;

use Tests\Integration\IntegrationTestCase;
use Models\GithubUser;

/**
 * @group integration
 * @group test-case
 */
class SimpleUseCaseTest extends IntegrationTestCase
{
    public function testSimpleEntityCase()
    {
        $user = new GithubUser('ikwattro', 'Christophe Willemsen');
        $this->uow->persist($user);
        $this->uow->commit();

        $q = 'MATCH (n:GithubUser {userName: {userName}}) RETURN n';
        $result = $this->getClient()->sendCypherQuery($q, ['userName' => $user->getUserName()])->getResult();
        $n = $result->get('n');

        $this->assertNotNull($n);
    }
}