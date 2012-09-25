<?php

/**
 * @file
 * Definition of Drupal\field\FieldItemInterface.
 */

namespace Drupal\field;

use Drupal\Core\Entity\Property\ItemInterface;

/**
 * Interface for field items.
 *
 * When implementing this interface which extends Traversable, make sure to list
 * IteratorAggregate or Iterator before this interface in the implements clause.
 */
interface FieldItemInterface extends ItemInterface {

  /**
   * Sets contextual information of the field item list.
   *
   * @param $entity_type
   *   The type of the field's entity.
   * @param $name
   *   The field name.
   * @param $bundle
   *   The bundle of the field's entity.
   */
  public function setFieldContext($entity_type, $name, $bundle);

  /**
   * Gets contextual information of the field item list.
   *
   * @return array|NULL
   *   If set, an array with the following entries:
   *   - entity type: The type of the field's entity.
   *   - name: The field name.
   *   - bundle: The bundle of the field's entity.
   */
  public function getFieldContext();

  /**
   * Get the field instance.
   *
   * @return array
   */
  public function getInstance();
}
