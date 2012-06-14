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
 *
 * This will be required for every property of an entity.
 * Delegate get() and set() magic as well as their magic equivalences to the
 * first item.
 */
interface EntityPropertyInterface extends PropertyListInterface, PropertyContainerInterface {

  /**
   * @return EntityPropertyItemInterface
   */
  public function offsetGet($offset);
}