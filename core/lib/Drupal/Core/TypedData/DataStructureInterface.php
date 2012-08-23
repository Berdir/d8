<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataStructureInterface.
 */

namespace Drupal\Core\TypedData;
use IteratorAggregate;

/**
 * Interface for data structures that contain properties.
 *
 * This is implemented by entities as well as by PropertyItem classes.
 */
interface DataStructureInterface extends IteratorAggregate  {

  /**
   * Gets an array of properties.
   *
   * The array contains all properties which are not computed.
   * @todo: How to get computed properties via the interface?
   *
   * @return array
   *   An array of property objects implementing the DataWrapperInterface, keyed
   *   by property name.
   */
  public function getProperties();

  /**
   * Sets an array of properties.
   *
   * @param array
   *   The array of properties to set. The array has to consist of property
   *   values or property objects implementing the DataWrapperInterface and must be
   *   keyed by property name.
   *
   * @throws \InvalidArgumentException
   *   If an not existing property is passed.
   * @throws \Drupal\Core\TypedData\DataReadOnlyException
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
   * Gets the the raw array representation of the contained properties.
   *
   * @return array
   *   The raw array representation of the contained properties, i.e. an array
   *   keyed by property name containing the raw values.
   */
  public function toArray();

}
