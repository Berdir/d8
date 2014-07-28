<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Schema\ContentEntitySchemaHandlerInterface.
 */

namespace Drupal\Core\Entity\Schema;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines an interface for handling the storage schema of entities.
 */
interface ContentEntitySchemaHandlerInterface {

  /**
   * Creates the schema for the given entity type definition.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   */
  public function createEntitySchema(ContentEntityTypeInterface $entity_type);

  /**
   * Drops the schema for the given entity type definition.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   */
  public function dropEntitySchema(ContentEntityTypeInterface $entity_type);

  /**
   * Updates the schema for the given entity type definition.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The updated entity type definition.
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $original
   *   The original entity type definition.
   */
  public function updateEntitySchema(ContentEntityTypeInterface $entity_type, ContentEntityTypeInterface $original);

  /**
   * Creates the storage schema for the given field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being created.
   */
  public function createFieldSchema(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Prepares the storage schema for the given field for deletion.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being deleted.
   */
  public function prepareFieldSchemaDeletion(FieldStorageDefinitionInterface $storage_definition);

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
