<?php

/*
 * @file
 * Definition of Drupal\field\FieldItemListInterface.
 */

namespace Drupal\field;
use Drupal\Core\Entity\Property\ItemListInterface;

/**
 * Interface for entity properties that are fields, being lists of field items.
 */
interface FieldItemListInterface extends ItemListInterface {

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
   * @throws \Drupal\Core\TypedData\MissingContextException
   *   If field context is not set.
   *
   * @see Drupal\field\FieldItemListInterface::setFieldContext()
   *
   * @return array
   */
  public function getInstance();
}
