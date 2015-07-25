<?php 
namespace QNetwork\Infrastructure\OGM\Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("CLASS")
*/
class Node extends OGMAnnotation{

	public $labels = [];
	public $repository = null;
	public $mapper = null;

}