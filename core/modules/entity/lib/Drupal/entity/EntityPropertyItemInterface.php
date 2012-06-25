<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItemInterface.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Interface for entity property items, which are property container that may
 * contain only primitives and entity references.
 *
 * @see EntityProperty
 * @see EntityPropertyItem
 */
interface EntityPropertyItemInterface extends PropertyContainerInterface {

  /**
   * May contain only primitives and entity references.
   *
   * In case of an entity reference it would return the entity object. You'll
   * have to go with getRawValue if you want the id.
   *
   * @return EntityInterface|mixed
   */
  public function get($property_name);
}