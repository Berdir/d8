<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityStorageControllerInterface.
 */

namespace Drupal\Core\Entity;
use Drupal\field\FieldInterface;
use Drupal\field\FieldInstanceInterface;

/**
 * Defines a common interface for entity controller classes.
 *
 * All entity controller classes specified via the "controllers['storage']" key
 * returned by \Drupal\Core\Entity\EntityManager or hook_entity_info_alter()
 * have to implement this interface.
 *
 * Most simple, SQL-based entity controllers will do better by extending
 * Drupal\Core\Entity\DatabaseStorageController instead of implementing this
 * interface directly.
 */
interface EntityStorageControllerInterface {

  /**
   * Resets the internal, static entity cache.
   *
   * @param $ids
   *   (optional) If specified, the cache is reset for the entities with the
   *   given ids only.
   */
  public function resetCache(array $ids = NULL);

  /**
   * Loads one or more entities.
   *
   * @param $ids
   *   An array of entity IDs, or NULL to load all entities.
   *
   * @return
   *   An array of entity objects indexed by their ids.
   */
  public function loadMultiple(array $ids = NULL);

  /**
   * Loads one entity.
   *
   * @param mixed $id
   *   The ID of the entity to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity object.
   */
  public function load($id);

  /**
   * Loads an unchanged entity from the database.
   *
   * @param mixed $id
   *   The ID of the entity to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The unchanged entity, or FALSE if the entity cannot be loaded.
   *
   * @todo Remove this method once we have a reliable way to retrieve the
   *   unchanged entity from the entity object.
   */
  public function loadUnchanged($id);

  /**
   * Load a specific entity revision.
   *
   * @param int $revision_id
   *   The revision id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|false
   *   The specified entity revision or FALSE if not found.
   */
  public function loadRevision($revision_id);

  /**
   * Delete a specific entity revision.
   *
   * A revision can only be deleted if it's not the currently active one.
   *
   * @param int $revision_id
   *   The revision id.
   */
  public function deleteRevision($revision_id);

  /**
   * Load entities by their property values.
   *
   * @param array $values
   *   An associative array where the keys are the property names and the
   *   values are the values those properties must have.
   *
   * @return array
   *   An array of entity objects indexed by their ids.
   */
  public function loadByProperties(array $values = array());

  /**
   * Constructs a new entity object, without permanently saving it.
   *
   * @param $values
   *   An array of values to set, keyed by property name. If the entity type has
   *   bundles the bundle key has to be specified.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A new entity object.
   */
  public function create(array $values);

  /**
   * Deletes permanently saved entities.
   *
   * @param array $entities
   *   An array of entity objects to delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures, an exception is thrown.
   */
  public function delete(array $entities);

  /**
   * Saves the entity permanently.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to save.
   *
   * @return
   *   SAVED_NEW or SAVED_UPDATED is returned depending on the operation
   *   performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures, an exception is thrown.
   */
  public function save(EntityInterface $entity);

  /**
   * Defines the base fields of the entity type.
   *
   * @return array
   *   An array of entity field definitions as specified by
   *   \Drupal\Core\Entity\EntityManager::getFieldDefinitions(), keyed by field
   *   name.
   *
   * @see \Drupal\Core\Entity\EntityManager::getFieldDefinitions()
   */
  public function baseFieldDefinitions();

  /**
   * Gets the name of the service for the query for this entity storage.
   *
   * @return string
   */
  public function getQueryServicename();

  /**
   * Invokes a method on the Field objects within an entity.
   *
   * @param string $method
   *   The method name.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   */
  public function invokeFieldMethod($method, EntityInterface $entity);

  /**
   * Invokes the prepareCache() method on all the relevant FieldItem objects.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   */
  public function invokeFieldItemPrepareCache(EntityInterface $entity);

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
   * Allows reaction to instance creation.
   *
   * @param \Drupal\field\FieldInstanceInterface $instance
   *   The instance created.
   * @param bool $first
   *   TRUE when the first instance is created, FALSE if other instances
   *   exist.
   */
  public function handleInstanceCreate(FieldInstanceInterface $instance, $first);

  /**
   * Allows reaction to the update of a configurable field.
   */
  public function handleFieldUpdate(FieldInterface $field, FieldInterface $original);

  /**
   * Allows reaction to the deletion of a configurable field.
   */
  public function handleFieldDelete(FieldInterface $field);

  /**
   * Allows reaction to the deletion of a configurable field instance.
   */
  public function handleInstanceDelete(FieldInstanceInterface $instance);

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
