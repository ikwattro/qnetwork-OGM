<?php 
namespace QNetwork\Infrastructure\OGM\Core;

/** 
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class InvalidIdException extends OGMException{

	protected $message = 'The Id you are trying to create is invalid.';

}