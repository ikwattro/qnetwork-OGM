<?php 
namespace Tests\OGM\Core;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Tests\OGMTest;
use Models\EmailAddress;
use Models\Password;
use Models\User;
use Meta\ReflectionClass;

class ReflectionClassTest extends OGMTest{

	protected $reflector = null;

	public function setUp(){
		
		$this->reflector = new ReflectionClass(new AnnotationReader(), new AnnotationRegistry());

	}

	public function testGetClassAnnotation(){

		$object = new EmailAddress('test@domain.com');
		$class = get_class($object);

		$annotation = $this->reflector->getClassAnnotation($class, 'Annotations\Node');
		$this->assertInstanceOf('Annotations\Node', $annotation);

	}

	public function testGetWrongClassAnnotation(){

		$object = new Password('123123');
		$class = get_class($object);

		$annotation = $this->reflector->getClassAnnotation($class, 'Annotations\Node');
		$this->assertNull($annotation);

	}
	
}
