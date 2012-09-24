<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\ComplexDataInterface.
 */

namespace Drupal\Core\TypedData;
use IteratorAggregate;

/**
 * Interface for complex data; i.e. data containing named and typed properties.
 *
 * This is implemented by entities as well as by property item classes of
 * the entity.module.
 */
interface ComplexDataInterface extends IteratorAggregate  {

  /**
   * Gets a property.
   *
   * @param $property_name
   *   The name of the property to get; e.g., 'title' or 'name'.
   *
   * @throws \InvalidArgumentException
   *   If an invalid property name is given.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The property object.
   */
  public function get($property_name);

  /**
   * Sets a property.
   *
   * @param $property_name
   *   The name of the property to set; e.g., 'title' or 'name'.
   * @param $value
   *   The value to set, or NULL to unset the property.
   *
   * @throws \InvalidArgumentException
   *   If an invalid property name is given.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The property object.
   */
  public function set($property_name, $value);

  /**
   * Gets an array of properties.
   *
   * @param bool $include_computed
   *   If set to TRUE, computed properties are included. Defaults to FALSE.
   *
   * @return array
   *   An array of property objects implementing the TypedDataInterface, keyed
   *   by property name.
   */
  public function getProperties($include_computed = FALSE);

  /**
   * Sets an array of properties.
   *
   * @param array
   *   The array of properties to set. The array has to consist of property
   *   values or property objects implementing the TypedDataInterface and must
   *   be keyed by property name.
   *
   * @throws \InvalidArgumentException
   *   If an not existing property is passed.
   * @throws \Drupal\Core\TypedData\ReadOnlyException
   *   If a read-only property is set.
   */
  public function setProperties($properties);

  /**
   * Gets the definition of a contained property.
   *
   * @param string $name
   *   The name of property.
   *
   * @return array|FALSE
   *   The definition of the property or FALSE if the property does not exist.
   */
  public function getPropertyDefinition($name);

  /**
   * Gets an array property definitions of contained properties.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   *
   * @return array
   *   An array of property definitions of contained properties, keyed by
   *   property name.
   */
  public function getPropertyDefinitions();

  /**
   * Gets the plain values of the contained properties.
   *
   * @return array
   *   An array keyed by property name containing the plain property values.
   */
  public function toArray();

  /**
   * Determines whether the data structure is empty.
   *
   * @return boolean
   *   TRUE if the data structure is empty, FALSE otherwise.
   */
  public function isEmpty();
}
