<?php

/**
 * @file
 * Definition of Drupal\entity\Property\EntityPropertyListInterface.
 */

namespace Drupal\entity\Property;
use Drupal\Core\TypedData\DataListInterface;
use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataAccessInterface;

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
interface EntityPropertyListInterface extends DataListInterface, DataWrapperInterface, DataAccessInterface {

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

}
