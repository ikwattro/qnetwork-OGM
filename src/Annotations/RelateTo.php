<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
class RelateTo extends OGMAnnotation{
	
	/**
	 * The relationship type present in Neo4j.
	 *
	 * @var string
	 */
	public $type = null;

	/**
	 * The direction of the relationship in Neo4j.
	 */
	public $direction = null;
	
	/**
	 * Eager loads this property.
	 *
	 * @var boolean
	 */
	public $collection = false;
	
}