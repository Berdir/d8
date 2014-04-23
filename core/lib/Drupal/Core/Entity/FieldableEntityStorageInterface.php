<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\FieldableEntityStorageInterface.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

interface FieldableEntityStorageInterface extends EntityStorageInterface {

  /**
   * Allows reaction to the creation of a field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field being created.
   */
  public function onFieldCreate(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Allows reaction to the update of a field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field being updated.
   */
  public function onFieldUpdate(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Allows reaction to the deletion of a field.
   *
   * Stored values should not be wiped at once, but marked as 'deleted' so that
   * they can go through a proper purge process later on.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field being deleted.
   *
   * @see fieldPurgeData()
   */
  public function onFieldDelete(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Allows reaction to the creation of a field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field being created.
   */
  public function onInstanceCreate(FieldDefinitionInterface $field_definition);

  /**
   * Allows reaction to the update of a field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field being updated.
   */
  public function onInstanceUpdate(FieldDefinitionInterface $field_definition);

  /**
   * Allows reaction to the deletion of a field.
   *
   * Stored values should not be wiped at once, but marked as 'deleted' so that
   * they can go through a proper purge process later on.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field being deleted.
   *
   * @see fieldPurgeData()
   */
  public function onInstanceDelete(FieldDefinitionInterface $field_definition);

  /**
   * Allows reaction to a bundle being created.
   *
   * @param string $bundle
   *   The name of the bundle created.
   */
  public function onBundleCreate($bundle);

  /**
   * Allows reaction to a bundle being renamed.
   *
   * This method runs before fields are updated with the new bundle name.
   *
   * @param string $bundle
   *   The name of the bundle being renamed.
   * @param string $bundle_new
   *   The new name of the bundle.
   */
  public function onBundleRename($bundle, $bundle_new);

  /**
   * Allows reaction to a bundle being deleted.
   *
   * This method runs before fields are deleted.
   *
   * @param string $bundle
   *   The name of the bundle being deleted.
   */
  public function onBundleDelete($bundle);

  /**
   * Purges the field data for a single field on a single entity.
   *
   * The entity itself is not being deleted, and it is quite possible that
   * other field data will remain attached to it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose field data is being purged.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The deleted field whose data is being purged.
   */
  public function onFieldItemsPurge(EntityInterface $entity, FieldDefinitionInterface $field_definition);

  /**
   * Performs final cleanup after all data of a field has been purged.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field being purged.
   */
  public function onFieldPurge(FieldStorageDefinitionInterface $storage_definition);

}
