<?php
/**
 * @file
 * Definition of Drupal\entity_test\TestEntityStorageController.
 */

namespace Drupal\entity_test;

use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityInterface;
use Drupal\entity\EntityStorageException;
use Drupal\Component\Uuid\Uuid;

/**
 * Storage controller for test entities.
 */
class TestEntityStorageController extends DatabaseStorageController {

  /**
   * The entity class to use.
   *
   * @todo: Remove this once this is moved in the main controller.
   *
   * @var string
   */
  protected $entityClass;

  /**
   * The entity bundle key.
   *
   * @var string|bool
   */
  protected $bundleKey;

  /**
   * Overrides DatabaseStorageController::__construct().
   */
  public function __construct($entityType) {
    parent::__construct($entityType);
    $this->bundleKey = !empty($this->entityInfo['entity keys']['bundle']) ? $this->entityInfo['entity keys']['bundle'] : FALSE;

    // Let load() get stdClass storage records. We map them to entities in
    // attachLoad().
    // @todo: Remove this once this is moved in the main controller.
    $this->entityClass = $this->entityInfo['entity class'];
    unset($this->entityInfo['entity class']);
  }

  /**
   * Overrides DatabaseStorageController::create().
   *
   * @todo: Remove this once this is moved in the main controller.
   */
  public function create(array $values) {

    $entity = new $this->entityClass($values, $this->entityType);

    // Assign a new UUID if there is none yet.
    if ($this->uuidKey && !isset($entity->{$this->uuidKey})) {
      $uuid = new Uuid();
      $entity->{$this->uuidKey} = $uuid->generate();
    }
    return $entity;
  }

  /**
   * Overrides DatabaseStorageController::attachLoad().
   *
   * Added mapping from storage records to entities.
   */
  protected function attachLoad(&$queried_entities, $revision_id = FALSE) {
    // Attach fields to the stdclass record first.
    if ($this->entityInfo['fieldable']) {
      if ($revision_id) {
        field_attach_load_revision($this->entityType, $queried_entities);
      }
      else {
        field_attach_load($this->entityType, $queried_entities);
      }
    }

    // Now map the record values to the according entity properties.
    $queried_entities = $this->mapFromStorageRecords($queried_entities);

    // Call hook_entity_load().
    foreach (module_implements('entity_load') as $module) {
      $function = $module . '_entity_load';
      $function($queried_entities, $this->entityType);
    }
    // Call hook_TYPE_load(). The first argument for hook_TYPE_load() are
    // always the queried entities, followed by additional arguments set in
    // $this->hookLoadArguments.
    $args = array_merge(array($queried_entities), $this->hookLoadArguments);
    foreach (module_implements($this->entityType . '_load') as $module) {
      call_user_func_array($module . '_' . $this->entityType . '_load', $args);
    }
  }

  /**
   * Overrides DatabaseStorageController::save().
   *
   * Added mapping from entities to storage records before saving.
   */
  public function save(EntityInterface $entity) {
    $transaction = db_transaction();
    try {
      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityType, $entity->id());
      }

      $this->preSave($entity);
      $this->invokeHook('presave', $entity);

      // Create the storage record to be saved.
      $record = $this->maptoStorageRecord($entity);

      if (!$entity->isNew()) {
        $return = drupal_write_record($this->entityInfo['base table'], $record, 'id');
        $this->resetCache(array($entity->{$this->idKey}));
        $this->postSave($entity, TRUE);
        $this->invokeHook('update', $entity);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base table'], $record);
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->{$this->idKey} = $record['id'];
        $entity->enforceIsNew(FALSE);
        $this->postSave($entity, FALSE);
        $this->invokeHook('insert', $entity);
      }

      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->original);

      return $return;
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Maps from storage records to entity objects.
   *
   * @return array
   *   An array of entity objects implementing the EntityInterface.
   */
  protected function mapFromStorageRecords(array $records) {

    foreach ($records as $id => $record) {
      $values = $this->bundleKey ? array($this->bundleKey => $record->{$this->bundleKey}) : array();

      $entity = new $this->entityClass($values, $this->entityType);
      $entity->{$this->idKey} = $id;
      $entity->{$this->uuidKey} = $record->uuid;
      $entity->langcode = $record->langcode;
      $entity->name->value = $record->name;
      $entity->user->id = $record->uid;

      $records[$id] = $entity;
    }
    return $records;
  }

  /**
   * Maps from storage records to entity objects.
   */
  protected function maptoStorageRecord(EntityInterface $entity) {
    // Handle base properties and ids.
    $record['id'] = $entity->id();
    $record['uuid'] = $entity->uuid;
    $record['langcode'] = $entity->langcode;
    $record['name'] = $entity->name->value;
    $record['uid'] = $entity->user->id;

    // @todo: Handle fields here.

    return $record;
  }

  /**
   * Implements \Drupal\entity\EntityStorageControllerInterface.
   */
  public function basePropertyDefinitions() {
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'text_item',
    );
    $properties['user'] = array(
      'label' => t('User'),
      'description' => t('The associated user.'),
      'type' => 'entityreference_item',
      'entity type' => 'user',
    );
    return $properties;
  }
}
