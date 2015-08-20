<?php 
namespace Mapping;
use Core\OGMException;
use Core\ObjectState;
use Core\LazyCollection;
use Meta\NodeEntity as MetaNodeEntity;
use ProxyManager\Proxy\LazyLoadingInterface as Proxy;

abstract class NodeMapper extends AbstractMapper{

	/**
	 * Takes an object and returns the matching cypher query;
	 * The second parameter $name represents the name of the matching entity in cypher;
	 * 
	 * @param DomainObject
	 * @param string
	 * @return string The match cypher query
	 */
	abstract public function match($object, $name = 'value');

	protected function getNodeProperties($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);
		
		// Gets all properties annotations (GraphProperty annotation) and their values present on object
		$annotations = $meta->getProperties();
		$values = $meta->getProperties($object);

		// map the annotations and values to a [key, property] array for Nodes
		$properties = [];
		foreach ($values as $propertyName => $value) {
			$properties[$annotations[$propertyName]->key] = $value;
			settype($properties[$annotations[$propertyName]->key], $annotations[$propertyName]->type);
		}
		
		return $properties;

	}

	public function mergeAllRelationships($object){

		$meta = $this->getUnitOfWork()->getClassMetadata($object);

		$annotations = $meta->getAssociations();
		$values = $meta->getAssociations($object);
		foreach ($values as $propertyName => $value) {

			$annotation = $annotations[$propertyName];
			// If $value instanceof LazyCollection then any elements removed have to be detached from the 
			// node at the database level; basically delete the relationship

			
			if( is_array($value) || $value instanceof \Traversable ){
				
				foreach ($value as $collectionValue) {
					if( $collectionValue instanceof Proxy && ! $collectionValue->isProxyInitialized() ){
						continue;
					}
					$this->mergeRelationship($object, $collectionValue, $annotation->type, $annotation->direction);
				}
				
				continue;

			}

			if($value === null){
				continue;
			}

			if( $value instanceof Proxy && ! $value->isProxyInitialized() ){
				continue;
			}

			$this->mergeRelationship($object, $value, $annotation->type, $annotation->direction);

		}

	}

	/**
	 * Merges a relationship between 2 nodes $from and $to
	 *
	 * @param DomainObject the start node
	 * @param DomainObject the end node
	 * @param string the type of the relationship
	 * @return void
	 */
	protected function mergeRelationship($from, $to, $type, $direction){
		
		// TODO: merge if and only if one of $from or $to is NEW or one of $from or $to is value object
		list($matchFrom, $paramsFrom) = $this->getUnitOfWork()->getMapper($from)->match($from, 'from');
		list($matchTo, $paramsTo) = $this->getUnitOfWork()->getMapper($to)->match($to, 'to');

		$params = array_merge($paramsFrom, $paramsTo);

		$query = $matchFrom . ' ' . $matchTo;
		$query .= " MERGE (from)-[r:{$type}]->(to) ";

		$this->addRelationshipStatement($query, $params);

	}

	/**
	 * Deletes the $type relationship between 2 nodes $from and $to .
	 * The OGM ensures by default that there is only one relationship
	 * of a given type between any 2 given domain objects.
	 *
	 * @param DomainObject the start node
	 * @param DomainObject the end node
	 * @param string the type of the relationship
	 * @return void
	 */
	protected function deleteRelationship($from, $type, $to = null){

		list($query, $params) = $this->match($from, 'from');
		
		if( $to ){

			list($toQuery, $toParams) = $this->match($to, 'to');
			$query .= ' ' . $toQuery;
			$params = array_merge($params, $toParams);

			$query .= " MATCH (from)-[r:{$type}]->(to) DELETE r";
		}

		if( ! $to ){
			$query .= " MATCH (from)-[r:{$type}]->() DELETE r";
		}

		$this->addRelationshipStatement($query, $params);

	}

}