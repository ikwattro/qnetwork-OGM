<?php 
namespace QNetwork\Infrastructure\OGM\Core;

use Neoxygen\NeoClient\ClientBuilder;
use Config;

/**
 * @author Cezar Grigore <grigorecezar@gmail.com>
 */
class Client {

	public static function create(){
		
		return ClientBuilder::create()
			->addConnection('default','http',
					Config::get('database.connections.neo4j.host'), 
					Config::get('database.connections.neo4j.port'), 
					true, 
					Config::get('database.connections.neo4j.username'),
					Config::get('database.connections.neo4j.password')
					)
				->setDefaultTimeout(180)
				->setAutoFormatResponse(true)
				->build();

	}

}