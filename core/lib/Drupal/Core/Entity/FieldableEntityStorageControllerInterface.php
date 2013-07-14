<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ExtensibleEntityStorageControllerInterface.
 */


namespace Drupal\Core\Entity;


use Drupal\field\FieldInterface;
use Drupal\field\FieldInstanceInterface;

interface FieldableEntityStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Allows reaction to the update of a configurable field.
   *
   * @param \Drupal\field\FieldInterface $field
   *   The field being updated.
   * @param \Drupal\field\FieldInterface $original
   *   The previous state of the field.
   */
  public function handleFieldUpdate(FieldInterface $field, FieldInterface $original);

  /**
   * Allows reaction to the deletion of a configurable field.
   *
   * @param \Drupal\field\FieldInterface $field
   *   The field being deleted.
   */
  public function handleFieldDelete(FieldInterface $field);

  /**
   * Allows reaction to the creation of a configurable field.
   *
   * @param \Drupal\field\FieldInterface $field
   *   The field being created.
   */
  public function handleFieldCreate(FieldInstanceInterface $field);

  /**
   * Allows reaction to the creation of a configurable field instance.
   *
   * @param \Drupal\field\FieldInstanceInterface $instance
   *   The instance being created.
   */
  public function handleInstanceCreate(FieldInstanceInterface $instance);

  /**
   * Allows reaction to the deletion of a configurable field instance.
   *
   * @param \Drupal\field\FieldInstanceInterface $instance
   *   The instance being deleted.
   */
  public function handleInstanceDelete(FieldInstanceInterface $instance);

  /**
   * Allows reaction to a bundle being created.
   *
   * @param string $bundle
   *   The name of the bundle created.
   */
  public function handleBundleCreate($bundle);

  /**
   * Allows reaction to a bundle being renamed.
   *
   * @param string $bundle
   *   The name of the bundle being renamed.
   * @param string $bundle_new
   *   The new name of the bundle.
   */
  public function handleBundleRename($bundle, $bundle_new);

  /**
   * Allows reaction to a bundle being deleted.
   *
   * @param string $bundle
   *   The name of the bundle being deleted.
   */
  public function handleBundleDelete($bundle);

  /**
   * Purges the field data for a single field on a single entity.
   *
   * The entity itself is not being deleted, and it is quite possible that
   * other field data will remain attached to it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose field data is being purged.
   * @param \Drupal\field\FieldInstanceInterface $instance
   *   The deleted field instance whose data is being purged.
   */
  public function fieldPurgeData(EntityInterface $entity, FieldInstanceInterface $instance);

  /**
   * Performs final cleanup after all data on all instances have been purged.
   *
   * @param \Drupal\field\FieldInterface $instance
   *   The field being purged.
   */
  public function fieldPurge(FieldInterface $field);

}
