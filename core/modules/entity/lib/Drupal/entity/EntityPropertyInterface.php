<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyInterface.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyListInterface;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Interface for entity properties, being lists of property items implementing delegation for working with the first item.
 *
 * Contained items must implement the EntityPropertyItemInterface.
 * This interface is required for every property of an entity.
 *
 * Methods of the EntityPropertyItemInterface which are not present in the
 * PropertyListInterface need to be delegated to the first contained
 * EntityPropertyItem, in particular that are get() and set() as well as their
 * magic equivalences.
 */
interface EntityPropertyInterface extends PropertyListInterface, EntityPropertyItemInterface {

  /**
   * @return EntityPropertyItemInterface
   */
  public function offsetGet($offset);
}
