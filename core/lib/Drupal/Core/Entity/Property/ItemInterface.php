<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Property\ItemInterface.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\TypedData\StructureInterface;
use Drupal\Core\TypedData\WrapperInterface;

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
   *
   * @param $property_name
   *   The name of the property to get; e.g., 'title' or 'name'.
   *
   * @throws \InvalidArgumentException
   *   If a not existing property is accessed.
   *
   * @return \Drupal\Core\TypedData\WrapperInterface
   *   The property object.
   */
  public function __get($property_name);

  /**
   * Magic setter: Set the property value.
   *
   * @param $property_name
   *   The name of the property to set; e.g., 'title' or 'name'.
   * @param $value
   *   The value to set, or NULL to unset the property.
   *
   * @throws \InvalidArgumentException
   *   If a not existing property is set.
   */
  public function __set($property_name, $value);

  /**
   * Magic method for isset().
   *
   * @param $property_name
   *   The name of the property to get; e.g., 'title' or 'name'.
   *
   * @return boolean
   *   Returns TRUE if the property exists and is set, FALSE otherwise.
   */
  public function __isset($property_name);

  /**
   * Magic method for unset().
   *
   * @param $property_name
   *   The name of the property to get; e.g., 'title' or 'name'.
   */
  public function __unset($property_name);
}
