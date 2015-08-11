<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("CLASS")
*/
class Mapper extends OGMAnnotation{

	public $namespace = null;

}