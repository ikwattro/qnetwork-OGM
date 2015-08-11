<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("CLASS")
*/
class Repository extends OGMAnnotation{

	public $namespace = null;

}