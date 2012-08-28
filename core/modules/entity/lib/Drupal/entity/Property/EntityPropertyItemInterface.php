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
   * Magic getter: Get the property value.
   */
  public function __get($name);

  /**
   * Magic setter: Set the property value.
   */
  public function __set($name, $value);
}
