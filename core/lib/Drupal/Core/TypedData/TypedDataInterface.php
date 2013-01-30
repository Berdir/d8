<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\TypedDataInterface.
 */

namespace Drupal\Core\TypedData;

use Drupal\user;

/**
 * Interface for typed data objects.
 */
interface TypedDataInterface {

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
   * @param mixed|null $value
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
