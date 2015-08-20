<?php 
namespace Annotations;
use Doctrine\Common\Annotations\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*/
class RelateTo extends OGMAnnotation{
	
	/**
	 * The relationship type.
	 *
	 * @var string
	 */
	public $type = null;

	/**
	 * The direction of the relationship.
	 */
	public $direction = null;
	
	/**
	 * Tells the OGM if this should be instantiated as a collection;
	 *
	 * @var boolean
	 */
	public $collection = false;

	/**
	 * To which object in the oop model does this object relate to.
	 *
	 * @var string
	 */
	public $reference = null;

	/**
	 * The objects property name; this is used by the reflector
	 */
	public $propertyName = null;
	
}