<?php

/**
 * @file
 * Definition of Drupal\entity\Property\EntityPropertyItemInterface.
 */

namespace Drupal\entity\Property;

use Drupal\Core\Data\DataStructureInterface;
use Drupal\Core\Data\DataItemInterface;

/**
 * Interface for entity property items, which are property container that may
 * contain only primitives and entity references.
 *
 * @see EntityPropertyList
 * @see EntityPropertyItem
 */
interface EntityPropertyItemInterface extends DataStructureInterface, DataItemInterface {

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
   * Returns a property.
   *
   * Entity property items may contain only primitives and entity references.
   *
   * @param string $property_name
   *   The name of the property to return; e.g., 'value'.
   *
   * @return Drupal\Core\Data\DataItemInterface
   *   The property object.
   */
  public function get($property_name);

  /**
   * Magic getter: Get the property value.
   */
  public function __get($name);

  /**
   * Magic setter: Set the property value.
   */
  public function __set($name, $value);
}
