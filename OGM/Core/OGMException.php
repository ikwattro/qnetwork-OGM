<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class OGMException extends \Exception{

	protected $code = 400;
	protected $message = 'There has been a problem with the object-to-graph mapping system.';

}