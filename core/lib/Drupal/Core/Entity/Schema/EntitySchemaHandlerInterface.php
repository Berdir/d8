<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Schema\EntitySchemaHandlerInterface.
 */

namespace Drupal\Core\Entity\Schema;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines an interface for handling the storage schema of entities.
 */
interface EntitySchemaHandlerInterface extends EntitySchemaProviderInterface {

  /**
   * Creates the storage schema for the given field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being created.
   */
  public function createFieldSchema(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Marks the storage schema for the given field as deleted.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being deleted.
   */
  public function markFieldSchemaAsDeleted(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Deletes the storage schema for the given field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being deleted.
   */
  public function deleteFieldSchema(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Updates the storage schema for the given field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being updated.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $original
   *   The original storage definition; i.e., the definition before the update.
   *
   * @throws \Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException
   *   Thrown when the update to the field is forbidden.
   */
  public function updateFieldSchema(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original);

}
