<?php 
namespace QNetwork\Infrastructure\OGM\Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("CLASS")
*/
class Relationship extends OGMAnnotation{

	public $type = null;
	public $direction = null;

}