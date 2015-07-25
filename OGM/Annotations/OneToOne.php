<?php 
namespace QNetwork\Infrastructure\OGM\Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
class OneToOne extends OGMAnnotation{
	
	public $type = null;
	
	/**
	 * Eager loads this property
	 *
	 * @var boolean
	 */
	public $eager = false;
	
}