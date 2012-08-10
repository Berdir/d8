<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyInterface.
 */

namespace Drupal\Core\Property;

/**
 * Interface for all properties.
 */
interface PropertyInterface {

  /**
   * Creates a property object given its definition.
   *
   * @param array $definition
   *   The definition of the property.
   * @param mixed $value
   *   (optional) The value of the property, or NULL if the property is not set.
   *    See PropertyInterface::setValue() for details.
   *
   *  @return PropertyContainerInterface
   */
  function __construct(array $definition, $value = NULL);

  /**
   * Gets the data type of the property.
   *
   * @return string
   *   The data type of the property.
   */
  public function getType();

  /**
   * Gets the definition of the property.
   *
   * @return array
   *   The definition of the property.
   */
  public function getDefinition();

  /**
   * Gets the value of the property.
   *
   * @return mixed
   */
  public function getValue();

  /**
   * Sets the value of the property.
   *
   * @param mixed $value
   *   The value to set in the format as documented for the property's type or
   *   NULL to unset the property.
   *
   * @throws InvalidValueException
   */
  public function setValue($value);

  /**
   * Returns a string representation of the property.
   *
   * @return string
   */
  public function getString();

  /**
   * Validates the property value.
   *
   * @param mixed $value
   *   (optional) If specified, the given value is validated. Otherwise the
   *   currently set value is validated.
   */
  public function validate($value = NULL);
}
