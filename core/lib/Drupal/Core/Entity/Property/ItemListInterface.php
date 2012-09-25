<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Property\ItemListInterface.
 */

namespace Drupal\Core\Entity\Property;

use Drupal\Core\TypedData\AccessibleInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Interface for entity properties, being lists of property items.
 *
 * Contained items must implement the ItemInterface. This interface is required
 * for every property of an entity. Some methods are delegated to the first
 * contained item, in particular get() and set() as well as their magic
 * equivalences.
 *
 * Optionally, a typed data object implementing
 * Drupal\Core\TypedData\TypedDataInterface may be passed to
 * ArrayAccess::offsetSet() instead of a plain value.
 *
 * When implementing this interface which extends Traversable, make sure to list
 * IteratorAggregate or Iterator before this interface in the implements clause.
 */
interface ItemListInterface extends ListInterface, TypedDataInterface, AccessibleInterface {

  /**
   * Delegates to the first item.
   *
   * @see \Drupal\Core\Entity\Property\ItemInterface::get()
   */
  public function get($property_name);

  /**
   * Magic getter: Delegates to the first item.
   *
   * @see \Drupal\Core\Entity\Property\ItemInterface::__get()
   */
  public function __get($property_name);

  /**
   * Magic setter: Delegates to the first item.
   *
   * @see \Drupal\Core\Entity\Property\ItemInterface::__set()
   */
  public function __set($property_name, $value);

  /**
   * Magic method for isset(): Delegates to the first item.
   *
   * @see \Drupal\Core\Entity\Property\ItemInterface::__isset()
   */
  public function __isset($property_name);

  /**
   * Magic method for unset(): Delegates to the first item.
   *
   * @see \Drupal\Core\Entity\Property\ItemInterface::__unset()
   */
  public function __unset($property_name);
}
