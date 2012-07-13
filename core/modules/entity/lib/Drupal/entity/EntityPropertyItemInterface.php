<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItemInterface.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Interface for entity property items, which are property container that may
 * contain only primitives and entity references.
 *
 * @see EntityProperty
 * @see EntityPropertyItem
 */
interface EntityPropertyItemInterface extends PropertyContainerInterface {

  /**
   * Check entity property access.
   *
   * @param \Drupal\user\User $account
   *   (optional) The user account to check access for. Defaults to the current
   *   user.
   *
   * @return bool
   *   Whether the given user has access.
   */
  public function access($account = NULL);

  /**
   * Gets the definition of the entity property.
   *
   * @return array
   *   The definition of the entity property.
   */
  public function getDefinition();

  /**
   * Returns the value of a contained property.
   *
   * Entity property items may contain only primitives and entity references.
   *
   * @param string $property_name
   *   The name of the property to return; e.g., 'value'.
   *
   * @return EntityInterface|mixed
   *   The property value, or NULL if it is not defined.
   */
  public function get($property_name);

  /**
   * Returns the raw value of a contained property.
   *
   * Returns the raw value, i.e. the id of the entity in case of entity
   * references, or the plain data of fields.
   *
   * @param string $property_name
   *   The name of the property to return; e.g., 'value'.
   *
   * @return mixed
   *   The raw property value, or NULL if it is not defined.
   */
  public function getRawValue($property_name);

  /**
   * Sets the value of a contained property.
   *
   * @param string $property_name
   *   The name of the contained property to set; e.g., 'value'.
   * @param mixed $value
   *   The value to set, or NULL to unset the property.
   */
  public function set($property_name, $value);

  /**
   * Magic getter.
   */
  public function __get($name);

  /**
   * Magic setter.
   */
  public function __set($name, $value);
}
