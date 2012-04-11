<?php

/**
 * @file
 * Definition of Drupal\entity\StorageControllerInterface.
 */

namespace Drupal\entity;

use DrupalEntityControllerInterface;

/**
 * Defines a common interface for entity storage controllers.
 */
interface StorageControllerInterface extends DrupalEntityControllerInterface {

  /**
   * Constructs a new entity object, without permanently saving it.
   *
   * @param $values
   *   An array of values to set, keyed by property name. If the entity type has
   *   bundles the bundle key has to be specified.
   *
   * @return EntityInterface
   *   A new entity object.
   */
  public function create(array $values);

  /**
   * Deletes permanently saved entities.
   *
   * @param $ids
   *   An array of entity IDs.
   *
   * @throws StorageException
   *   In case of failures, an exception is thrown.
   */
  public function delete($ids);

  /**
   * Saves the entity permanently.
   *
   * @param EntityInterface $entity
   *   The entity to save.
   *
   * @return
   *   SAVED_NEW or SAVED_UPDATED is returned depending on the operation
   *   performed.
   *
   * @throws StorageException
   *   In case of failures, an exception is thrown.
   */
  public function save(EntityInterface $entity);
}
