<?php

/**
 * @file
 * Definition of Drupal\entity\Property\EntityPropertyItemInterface.
 */

namespace Drupal\entity\Property;

use Drupal\Core\TypedData\DataStructureInterface;
use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataAccessibleInterface;

/**
 * Interface for entity property items, which are property container that may
 * contain only primitives and entity references.
 *
 * @see EntityPropertyList
 * @see EntityPropertyItem
 */
interface EntityPropertyItemInterface extends DataStructureInterface, DataWrapperInterface {

  /**
   * Returns a property.
   *
   * Entity property items may contain only primitives and entity references.
   *
   * @param string $property_name
   *   The name of the property to return; e.g., 'value'.
   *
   * @return Drupal\Core\TypedData\DataWrapperInterface
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
