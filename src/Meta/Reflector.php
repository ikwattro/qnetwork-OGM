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

	/**
	 * Gets all properties of $class that have $annotation.
	 * @param string full class namespace
	 * @param string full annotation namespace
	 * @return OGMAnnotation | null returns the specific annotation or null if class doesn't have $annotation
	 */
	public function getPropertiesWithAnnotation($class, $annotation);

	/**
	 * Gets the value that the object provided holds for the property provided.
	 * 
	 * @param DomainObject The object that we need to find the property value for
	 * @param string The property name present on the object
	 * @return DomainObject | Collection
	 */
	public function getObjectPropertyValue($object, $propertyName);

	/**
	 * Sets property for a specific object.
	 *
	 * @param object 
	 * @param string property that should be changed
	 * @param string the value that the property should take
	 * @return this
	 */
	public function setPropertyValueForObject($object, $property, $value);

	/**
	 * Instantiate any class without using the constructor.
	 *
	 * @param string The class that should be instantiated
	 * @return object
	 */
	public function newInstanceWithoutConstructor($class);

}