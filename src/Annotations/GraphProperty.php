<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
class GraphProperty extends OGMAnnotation{

	public $type = null;
	public $key = null;

	public $match = false;
	public $reference = null;

}