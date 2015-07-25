<?php 
namespace QNetwork\Infrastructure\OGM\Core;
use QNetwork\Infrastructure\OGM\Meta\MetaMapping;

/**
 * @author Cezar Grigore 
 *
 * This is an abstraction of anything that is a domain object; check DDD;
 *
 * Any Entity or Relationship with meaning is considered to be a DomainObject.
 *
 * All the domain objects are being mapped to the graph (database).
 *
 * Each DomainObject must have a mapper so the UnitOfWork knows how 
 * to map them to the graph database. The UnitOfWork also keeps track of all
 * domain objects in memory and the ones that have had some interaction with the db.
 *
 * Some of the domain objects have repositories, mostly depending on if they are
 * aggregate roots or not. 
 */
abstract class DomainObject {

	public function __construct(){}

	/**
	 * @return boolean
	 */
	abstract public function isEntity();
	
	/**
	 * This returns the repository attached to this domain object.
	 * The repository is being pulled using a binding in the App namespace,
	 * more specifically RepositoryServiceProvider, between an interface and a 
	 * specific implementation that sits in the Infrastructure namespace and 
	 * knows how to communicate with the database. This way we maintain separation 
	 * of concerns and decoupling of the Domain layer from the Infrastructure layer.
	 *
	 * @return QNetwork\Infrastructure\OGM\Repositories\AbstractRepository
	 */
	public static function getRepository(){
		
		$unitOfWork = \App::make('QNetwork\Infrastructure\OGM\Core\UnitOfWork');
		$class = get_called_class();
		
		return $unitOfWork->getRepository($class);

	}
	
	/**
	 * This returns the mapper attached to this domain object.
	 * Using a binding in the App namespace we are decoupling the Domain layer
	 * from the Infrastructure layer, while maintaing a easy of way of getting
	 * the data mapper for any domain object, this way making the developer's life
	 * easier when needing the mapper in the unit of work or other places.
	 *
	 * @return QNetwork\Infrastructure\OGM\Mappers\AbstractMapper
	 */
	public static function getMapper(){

		$unitOfWork = \App::make('QNetwork\Infrastructure\OGM\Core\UnitOfWork');
		$class = get_called_class();
		
		return $unitOfWork->getMapper($class);

	}
}