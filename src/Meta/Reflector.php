<?php 
namespace Meta;

interface Reflector{

	/**
	 * Gets the specific annotation object for a class namespace given the annotation name.
	 * Used for Node/Relationship, Entity/ValueObject, Repository, Mapper;
	 * If annotation not present returns NULL;
	 *
	 * @param string full class namespace
	 * @param string full annotation namespace
	 * @return OGMAnnotation | null returns the specific annotation or null if class doesn't have $annotation
	 */
	public function getClassAnnotation($class, $annotation);

}