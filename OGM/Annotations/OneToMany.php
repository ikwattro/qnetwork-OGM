<?php 
namespace QNetwork\Infrastructure\OGM\Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
class OneToMany extends OGMAnnotation{

	public $type = null;
	
}