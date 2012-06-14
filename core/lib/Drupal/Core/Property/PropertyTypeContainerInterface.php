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
   * Given an associative array of property values matching the definitions,
   * create an instance of the data type.
   */
  function createItem($values);
}
