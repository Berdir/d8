<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\TypedDataInterface.
 */

namespace Drupal\Core\TypedData;
use Drupal\user;

/**
 * Interface for typed data objects.
 */
interface TypedDataInterface {

  /**
   * Creates a typed data object given its definition.
   *
   * @param array $definition
   *   The data definition.
   *
   * @see Drupal\Core\TypedData\TypedDataManager::create()
   */
  public function __construct(array $definition);

  /**
   * Sets the context of the typed data object.
   *
   * @param array $context
   *   (optional) An array describing the context of the data object, e.g. its
   *   name or parent data structure. The context should be passed if a typed
   *   data object is created as part of a data structure. The following keys
   *   are supported:
   *   - name: The name associated with the data.
   *   - parent: The parent object containing the data. Must be an instance of
   *     Drupal\Core\TypedData\ComplexDataInterface or
   *     Drupal\Core\TypedData\ListInterface.
   */
  public function setContext(array $context);

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
