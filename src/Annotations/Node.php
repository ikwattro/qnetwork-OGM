<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("CLASS")
*/
class Node extends OGMAnnotation{

	public $labels = [];

}