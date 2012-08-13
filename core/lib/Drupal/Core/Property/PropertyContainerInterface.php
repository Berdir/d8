<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyContainerInterface.
 */

namespace Drupal\Core\Property;
use IteratorAggregate;

/**
 * Interface for data structures that contain properties.
 *
 * This is implemented by entities as well as by PropertyItem classes.
 */
interface PropertyContainerInterface extends IteratorAggregate  {

  /**
   * Gets an array of properties.
   *
   * The array contains all properties which are not computed.
   * @todo: How to get computed properties via the interface?
   *
   * @return array
   *   An array of property objects implementing the PropertyInterface, keyed
   *   by property name.
   */
  public function getProperties();

  /**
   * Sets an array of properties.
   *
   * @param array
   *   The array of properties to set. The array has to consist of property
   *   objects implementing the PropertyInterface and must be keyed by property
   *   name.
   *
   * @throws \InvalidArgumentException
   *   If an not existing property is passed.
   * @throws \Drupal\Core\Property\PropertyReadOnlyException
   *   If a read-only property is set.
   */
  public function setProperties($properties);

  /**
   * Gets the definition of a contained property.
   *
   * @param string $name
   *   The name of property.
   *
   * @return array
   *   The definition of the property.
   */
  public function getPropertyDefinition($name);

  /**
   * Gets an array property definitions of contained properties.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
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
