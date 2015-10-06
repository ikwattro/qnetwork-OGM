<?php

namespace Tests\Integration;

use Core\NeoClient;
use Meta\ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Core\UnitOfWork;
use Meta\MetadataFactory;

class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Core\UnitOfWork
     */
    protected $uow;

    public function setUp()
    {
        $this->faker = \Faker\Factory::create();
        $credentials['connection'] = 'http';
        $credentials['host'] = 'localhost';
        $credentials['port'] = 7474;
        $credentials['username'] = 'neo4j';
        $credentials['password'] = '123123';

        $reflector = new ReflectionClass(new AnnotationReader(), new AnnotationRegistry());
        $this->uow = new UnitOfWork(
            new NeoClient($credentials), new MetadataFactory($reflector)
        );
    }

    /**
     * @return \Neoxygen\NeoClient\Client
     */
    protected function getClient()
    {
        return $this->uow->getManager()->getClient();
    }
}