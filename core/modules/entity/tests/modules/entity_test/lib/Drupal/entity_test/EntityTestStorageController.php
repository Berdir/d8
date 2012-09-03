<?php

/**
 * @file
 * Definition of Drupal\entity_test\EntityTestStorageController.
 */

namespace Drupal\entity_test;

use PDO;

use Drupal\entity\EntityInterface;
use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityStorageException;
use Drupal\Component\Uuid\Uuid;

/**
 * Defines the controller class for the test entity.
 *
 * This extends the Drupal\entity\DatabaseStorageController class, adding
 * required special handling for test entities.
 */
class EntityTestStorageController extends DatabaseStorageController {

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
   * @todo: Remove this once this is moved in the main controller.
   */
  public function create(array $values) {
    // Pass in default values.
    $defaults = array();
    $defaults['langcode'][LANGUAGE_NOT_SPECIFIED][0]['value'] = LANGUAGE_NOT_SPECIFIED;

    $entity = new $this->entityClass(array('values' => $defaults), $this->entityType);

    // Make sure to set the bundle first.
    if ($this->bundleKey) {
      $entity->{$this->bundleKey} = $values[$this->bundleKey];
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
   * Overrides Drupal\entity\DatabaseStorageController::loadByProperties().
   */
  public function loadByProperties(array $values) {
    $query = db_select($this->entityInfo['base table'], 'base');
    $query->addTag($this->entityType . '_load_multiple');
    if ($values) {
      // Conditions need to be applied the property data table.
      $query->addJoin('inner', 'entity_test_property_data', 'data', "base.{$this->idKey} = data.{$this->idKey}");
      $query->distinct(TRUE);

      // @todo We should not be using a condition to specify whether conditions
      // apply to the default language or not. We need to move this to a
      // separate parameter during the following API refactoring.
      // Default to the original entity language if not explicitly specified
      // otherwise.
      if (!array_key_exists('default_langcode', $values)) {
        $values['default_langcode'] = 1;
      }
      // If the 'default_langcode' flag is explicitly not set, we do not care
      // whether the queried values are in the original entity language or not.
      elseif ($values['default_langcode'] === NULL) {
        unset($values['default_langcode']);
      }

      $data_schema = drupal_get_schema('entity_test_property_data');
      $query->addField('data', $this->idKey);
      foreach ($values as $field => $value) {
        // Check on which table the condition needs to be added.
        $table = isset($data_schema['fields'][$field]) ? 'data' : 'base';
        $query->condition($table . '.' . $field, $value);
      }
    }
    $ids = $query->execute()->fetchCol();
    return $ids ? $this->load($ids) : array();
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

    // Load data of translatable properties.
    $this->attachPropertyData($queried_entities);

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

      $entity->id[LANGUAGE_NOT_SPECIFIED][0]['value'] = $id;
      $entity->uuid[LANGUAGE_NOT_SPECIFIED][0]['value'] = $record->uuid;
      $entity->langcode[LANGUAGE_NOT_SPECIFIED][0]['value'] = $record->langcode;

      $records[$id] = $entity;
    }
    return $records;
  }

  /**
   * Attaches property data in all languages for translatable properties.
   */
  protected function attachPropertyData(&$queried_entities) {
    $data = db_select('entity_test_property_data', 'data', array('fetch' => PDO::FETCH_ASSOC))
      ->fields('data')
      ->condition('id', array_keys($queried_entities))
      ->orderBy('data.id')
      ->execute();

    foreach ($data as $values) {
      $id = $values['id'];
      // Property values in default language are stored with
      // LANGUAGE_NOT_SPECIFIED as key.
      $langcode = empty($values['default_langcode']) ? $values['langcode'] : LANGUAGE_NOT_SPECIFIED;

      $queried_entities[$id]->name[$langcode][0]['value'] = $values['name'];
      $queried_entities[$id]->user_id[$langcode][0]['value'] = $values['user_id'];
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

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   *
   * Stores values of translatable properties.
   */
  protected function postSave(EntityInterface $entity, $update) {
    $default_langcode = $entity->language()->langcode;

    foreach ($entity->getTranslationLanguages() as $langcode => $language) {
      $translation = $entity->getTranslation($langcode);

      $values = array(
        'id' => $entity->id(),
        'langcode' => $langcode,
        'default_langcode' => intval($default_langcode == $langcode),
        'name' => $translation->name->value,
        'user_id' => $translation->user_id->value,
      );

      db_merge('entity_test_property_data')
        ->fields($values)
        ->condition('id', $values['id'])
        ->condition('langcode', $values['langcode'])
        ->execute();
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postDelete().
   */
  protected function postDelete($entities) {
    db_delete('entity_test_property_data')
      ->condition('id', array_keys($entities))
      ->execute();
  }

  /**
   * Overrides \Drupal\entity\DataBaseStorageController::basePropertyDefinitions().
   */
  public function basePropertyDefinitions() {
    $properties['id'] = array(
      'label' => t('ID'),
      'description' => ('The ID of the test entity.'),
      'type' => 'integer_item',
      'list' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => ('The UUID of the test entity.'),
      'type' => 'string_item',
      'list' => TRUE,
    );
    $properties['langcode'] = array(
      'label' => t('Language code'),
      'description' => ('The language code of the test entity.'),
      'type' => 'language_item',
      'list' => TRUE,
    );
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'string_item',
      'list' => TRUE,
      'translatable' => TRUE,
    );
    $properties['user_id'] = array(
      'label' => t('User ID'),
      'description' => t('The ID of the associated user.'),
      'type' => 'entityreference_item',
      'settings' => array('entity type' => 'user'),
      'list' => TRUE,
      'translatable' => TRUE,
    );
    return $properties;
  }

  /**
   * Overrides \Drupal\entity\DataBaseStorageController::cacheGet().
   */
  protected function cacheGet($ids, $conditions = array()) {
    $entities = parent::cacheGet($ids, array());

    // Exclude any entities loaded from cache if they don't match $conditions.
    // This ensures the same behavior whether loading from memory or database.
    if ($conditions) {
      if (!$ids) {
        $entities = $this->entityCache;
      }

      foreach ($entities as $entity) {
        $entity_values = $entity->toArray();
        if (array_diff_assoc($conditions, $entity_values)) {
          unset($entities[$entity->id()]);
        }
      }
    }
    return $entities;
  }
}
