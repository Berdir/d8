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
 * @todo: Should getProperties(), setProperties() and getPropertyDefinitions()
 * be delegated as well.
 */
interface EntityPropertyInterface extends PropertyListInterface {

  /**
   * Delegated to the first item.
   *
   * @see EntityPropertyItemInterface::get()
   */
  public function get($property_name);

  /**
   * Magic getter: Delegated to the first item.
   */
  public function __get($name);

  /**
   * Magic setter: Delegated to the first item.
   */
  public function __set($name, $value);

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

}
