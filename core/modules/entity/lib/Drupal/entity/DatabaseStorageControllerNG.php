<?php

/**
 * @file
 * Definition of Drupal\entity\DatabaseStorageControllerNG.
 */

namespace Drupal\entity;

use PDO;

use Drupal\entity\EntityInterface;
use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityStorageException;
use Drupal\Component\Uuid\Uuid;

/**
 * Implements Property API specific enhancements to the DatabaseStorageController class.
 *
 * @todo: Once all entity types have been converted, merge improvements into the
 * DatabaseStorageController class.
 */
class DatabaseStorageControllerNG extends DatabaseStorageController {

  /**
   * The entity class to use.
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
    $this->entityClass = $this->entityInfo['entity class'];

    // Work-a-round to let load() get stdClass storage records without having to
    // override it. We map storage records to entities in
    // DatabaseStorageControllerNG:: mapFromStorageRecords().
    // @todo: Remove this once this is moved in the main controller.
    unset($this->entityInfo['entity class']);
  }

  /**
   * Overrides DatabaseStorageController::create().
   *
   * @param array $values
   *   An array of values to set, keyed by property name. The value has to be
   *   the plain value of an entity property, i.e. an array of property items.
   *   If no array is given, the value will be set for the first property item.
   *   Thus to set the first item of a 'name' property one can pass:
   *   @code
   *     $values = array('name' => array(0 => array('value' => 'the name')));
   *   @endcode
   *   or
   *   @code
   *     $values = array('name' => array('value' => 'the name'));
   *   @endcode
   *
   *   Furthermore, property items having only a single value support setting
   *   this value without passing an array of values, making it possible to
   *   set the 'name' property via:
   *   @code
   *     $values = array('name' => 'the name');
   *   @endcode
   *
   * @return Drupal\entity\EntityInterface
   *   A new entity object.
   */
  public function create(array $values) {
    // Pass in default values.
    $defaults = array();
    $defaults['langcode'][LANGUAGE_NOT_SPECIFIED][0]['value'] = LANGUAGE_NOT_SPECIFIED;

    $entity = new $this->entityClass(array('values' => $defaults), $this->entityType);

    // Make sure to set the bundle first.
    if ($this->bundleKey) {
      $entity->{$this->bundleKey}[0] = $values[$this->bundleKey];
      unset($values[$this->bundleKey]);
    }

    // Set all other given values.
    foreach ($values as $name => $value) {
      if (is_array($value) && is_numeric(current(array_keys($value)))) {
        $entity->$name = $value;
      }
      else {
        // Support passing in the first value of a property item.
        $entity->{$name}[0] = $value;
      }
    }

    // Assign a new UUID if there is none yet.
    if ($this->uuidKey && !isset($entity->{$this->uuidKey})) {
      $uuid = new Uuid();
      $entity->{$this->uuidKey}->value = $uuid->generate();
    }
    return $entity;
  }

  /**
   * Overrides DatabaseStorageController::attachLoad().
   *
   * Added mapping from storage records to entities.
   */
  protected function attachLoad(&$queried_entities, $revision_id = FALSE) {
    // Now map the record values to the according entity properties and
    // activate compatibility mode.
    $queried_entities = $this->mapFromStorageRecords($queried_entities);

    parent::attachLoad($queried_entities, $revision_id);

    // Loading is finished, so disable compatibility mode now.
    foreach ($queried_entities as $entity) {
      $entity->setCompatibilityMode(FALSE);
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
      $entity = new $this->entityClass(array(), $this->entityType);
      $entity->setCompatibilityMode(TRUE);

      foreach ($record as $name => $value) {
        $entity->{$name}[LANGUAGE_DEFAULT][0]['value'] = $value;
      }
      $records[$id] = $entity;
    }
    return $records;
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
      // Update the original values so that the compatibility mode works with
      // the update values, what is required by field API attachers.
      // @todo Once field API has been converted to use the Property API, move
      // this after insert/update hooks.
      $entity->updateOriginalValues();

      if (!$entity->isNew()) {
        $return = drupal_write_record($this->entityInfo['base table'], $record, 'id');
        $this->resetCache(array($entity->id()));
        $this->postSave($entity, TRUE);
        $this->invokeHook('update', $entity);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base table'], $record);
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->{$this->idKey}->value = $record->id;
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
   * Overrides DatabaseStorageController::invokeHook().
   *
   * Invokes field API attachers in compatibility mode and disables it
   * afterwards.
   */
  protected function invokeHook($hook, EntityInterface $entity) {
    if (!empty($this->entityInfo['fieldable']) && function_exists($function = 'field_attach_' . $hook)) {
      $entity->setCompatibilityMode(TRUE);
      $function($this->entityType, $entity);
      $entity->setCompatibilityMode(FALSE);
    }

    // Invoke the hook.
    module_invoke_all($this->entityType . '_' . $hook, $entity);
    // Invoke the respective entity-level hook.
    module_invoke_all('entity_' . $hook, $entity, $this->entityType);
  }

  /**
   * Maps from an entity object to the storage record of the base table.
   */
  protected function mapToStorageRecord(EntityInterface $entity) {
    $record = new \stdClass();
    $record->id = $entity->id();
    $record->langcode = $entity->langcode->value;
    $record->uuid = $entity->uuid->value;
    return $record;
  }
}
