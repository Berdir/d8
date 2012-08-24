<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataWrapperInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for typed data wrappers.
 */
interface DataWrapperInterface {

  /**
   * Creates a wrapper object given its definition and value.
   *
   * @param array $definition
   *   The data definition.
   * @param mixed $value
   *   (optional) The data value, or NULL if the it is not set. See
   *   DataWrapperInterface::setValue() for details.
   * @param $context
   *   (optional) An array describing the data's context. Allows data structures
   *   to pass on context to derived property wrappers. The following keys are
   *   supported:
   *   - name: The name of the property being created.
   *   - parent: The parent object containing the property. Must be an instance
   *     of \Drupal\Core\TypedData\DataStructureInterface.
   *
   * @see drupal_get_property()
   */
  public function __construct(array $definition, $value = NULL, $context = array());

  /**
   * Gets the data type.
   *
   * @return string
   *   The data type of the wrapped data.
   */
  public function getType();

  /**
   * Gets the data definition.
   *
   * @return array
   *   The data definition array.
   */
  public function getDefinition();

  /**
   * Gets the data value.
   *
   * @return mixed
   */
  public function getValue();

  /**
   * Sets the data value.
   *
   * @param mixed $value
   *   The value to set in the format as documented for the data type or NULL to
   *   unset the data value.
   *
   * @throws \Drupal\Core\TypedData\DataReadOnlyException
   *   If the data is read-only.
   */
  public function setValue($value);

  /**
   * Returns a string representation of the data.
   *
   * @return string
   */
  public function getString();

  /**
   * Validates the data value.
   *
   * @param mixed $value
   *   (optional) If specified, the given value is validated. Otherwise the
   *   currently set value is validated.
   */
  public function validate($value = NULL);
}
