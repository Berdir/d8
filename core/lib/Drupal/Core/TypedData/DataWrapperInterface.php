<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataWrapperInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for all properties.
 */
interface DataWrapperInterface {

  /**
   * Creates a property object given its definition.
   *
   * @param array $definition
   *   The definition of the property.
   * @param mixed $value
   *   (optional) The value of the property, or NULL if the property is not set.
   *    See DataWrapperInterface::setValue() for details.
   * @param $context
   *   (optional) An array describing the context of the property. It should be
   *   passed if a property is created as part of a property container. The
   *   following keys are supported:
   *   - name: The name of the property being created.
   *   - parent: The parent object containing the property. Must be an instance of
   *     \Drupal\Core\TypedData\DataStructureInterface.
   *
   * @see drupal_get_property()
   */
  public function __construct(array $definition, $value = NULL, $context = array());

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
   * @throws \Drupal\Core\TypedData\DataReadOnlyException
   *   If the property is read-only.
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
