<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\WrapperInterface.
 */

namespace Drupal\Core\TypedData;
use Drupal\user;

/**
 * Interface for typed data wrappers.
 */
interface WrapperInterface {

  /**
   * Creates a wrapper object given its definition and value.
   *
   * @param array $definition
   *   The data definition.
   * @param mixed $value
   *   (optional) The data value, or NULL if the it is not set. See
   *   WrapperInterface::setValue() for details.
   * @param array $context
   *   (optional) An array describing the context of the data. It should be
   *   passed if a data value is wrapped as part of a data structure. The
   *   following keys are supported:
   *   - name: The name of the data being wrapped.
   *   - parent: The parent object containing the data. Must be an instance of
   *     \Drupal\Core\TypedData\StructureInterface.
   *
   * @see drupal_wrap_data()
   */
  public function __construct(array $definition, $value = NULL, array $context = array());

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
   * @throws \Drupal\Core\TypedData\ReadOnlyException
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
   * Validates the currently set data value.
   */
  public function validate();

}
