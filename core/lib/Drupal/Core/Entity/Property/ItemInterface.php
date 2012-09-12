<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Property\ItemInterface.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\TypedData\StructureInterface;
use Drupal\Core\TypedData\WrapperInterface;
use Drupal\Core\TypedData\AccessibleInterface;

/**
 * Interface for entity property items, which are data structures holding
 * the values.
 *
 * @see EntityPropertyList
 * @see ItemBase
 */
interface ItemInterface extends StructureInterface, WrapperInterface {

  /**
   * Magic getter: Get the property value.
   */
  public function __get($name);

  /**
   * Magic setter: Set the property value.
   */
  public function __set($name, $value);
}
