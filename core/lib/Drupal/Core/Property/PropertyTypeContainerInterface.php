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

  /**
   * Gets the property object given the raw value.
   *
   * @todo: Currently this handles list of properties as well. Maybe we want to
   * separate that out, so plugins are either for lists or non-lists?
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   * @param mixed $value
   *   The raw value of the property, or NULL if the property is not set. For
   *   entity references the raw value is the entity ID, whereas for other
   *   property containers it is usually an array of (raw) values matching the
   *   definitions of the contained properties.
   *
   *  @return PropertyContainerInterface
   */
  function getProperty(array $definition, $value = NULL);

  /**
   * Gets the raw value of a property container object.
   *
   * @todo: Currently this handles list of properties as well. Maybe we want to
   * separate that out?
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   * @param PropertyContainerInterface|PropertyListInterface $value
   *
   * @return mixed
   */
  function getRawValue(array $definition, $value);
}
