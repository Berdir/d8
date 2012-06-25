<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyTypeContainerInterface.
 */

namespace Drupal\Core\Property;

/**
 * Once per field type and one for entities.
 */
interface PropertyTypeContainerInterface extends PropertyTypeInterface {

  /**
   * Gets an array property definitions of contained properties.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   */
  function getPropertyDefinitions(array $definition);
  // Implementation for entities relies upon the Entity::getPropertyDefinition
  // implementation.

  /**
   * Create an item of the data type.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   * @param array $values
   *   An array of property values for creating the data item. The property
   *   values have to match their respective definitions as returned from
   *   PropertyTypeContainerInterface::getPropertyDefinitions().
   *
   *  @return PropertyContainerInterface
   */
  function createItem(array $definition, array $values);
}
