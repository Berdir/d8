<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyInterface.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyListInterface;

/**
 * Interface for entity properties, being lists of property items.
 *
 * Contained items must implement the EntityPropertyItemInterface. This
 * interface is required for every property of an entity.
 *
 * Some methods are delegated to the first contained EntityPropertyItem, in
 * particular get() and set() as well as their magic equivalences.
 *
 * @todo: Should getProperties() and getPropertyDefinitions() be delegated
 * as well.
 */
interface EntityPropertyInterface extends PropertyListInterface {

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
   * Delegated to the first item.
   *
   * @see EntityPropertyItemInterface::get()
   */
  public function get($property_name);

  /**
   * Delegated to the first item.
   *
   * @see EntityPropertyItemInterface::getRawValue()
   */
  public function getRawValue($property_name);

  /**
   * Delegated to the first item.
   *
   * @see EntityPropertyItemInterface::set()
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
