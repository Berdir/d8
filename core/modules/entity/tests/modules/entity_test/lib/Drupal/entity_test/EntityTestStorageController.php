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
   * @todo: Remove this once this is moved in the main controller.
   */
  public function create(array $values) {
    // Pass in default values.
    $defaults = array();
    $defaults['language'][LANGUAGE_NOT_SPECIFIED][0]['langcode'] = LANGUAGE_NOT_SPECIFIED;

    $entity = new $this->entityClass(array('values' => $defaults), $this->entityType);

    // Make sure to set the bundle first.
    if ($this->bundleKey) {
      $entity->{$this->bundleKey} = $values[$this->bundleKey];
      unset($values[$this->bundleKey]);
    }

    // Set all other given values.
    foreach ($values as $name => $value) {
      $entity->$name = $value;
    }

    // Assign a new UUID if there is none yet.
    if ($this->uuidKey && !isset($entity->{$this->uuidKey})) {
      $uuid = new Uuid();
      $entity->{$this->uuidKey}->value = $uuid->generate();
    }
    return $entity;
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::buildQuery().
   */
  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);

    if ($conditions) {
      // Reset conditions as the default storage controller applies them to the
      // base table.
      $query_conditions = &$query->conditions();
      $query_conditions = array('#conjunction' => 'AND');

      // Restore id conditions.
      if ($ids) {
        $query->condition("base.{$this->idKey}", $ids, 'IN');
      }

      // Conditions need to be applied the property data table.
      $query->addJoin('inner', 'entity_test_property_data', 'data', "base.{$this->idKey} = data.{$this->idKey}");
      $query->distinct(TRUE);

      // @todo We should not be using a condition to specify whether conditions
      // apply to the default language or not. We need to move this to a
      // separate parameter during the following API refactoring.
      // Default to the original entity language if not explicitly specified
      // otherwise.
      if (!array_key_exists('default_langcode', $conditions)) {
        $conditions['default_langcode'] = 1;
      }
      // If the 'default_langcode' flag is explicitly not set, we do not care
      // whether the queried values are in the original entity language or not.
      elseif ($conditions['default_langcode'] === NULL) {
        unset($conditions['default_langcode']);
      }

      foreach ($conditions as $field => $value) {
        $query->condition('data.' . $field, $value);
      }
    }

    return $query;
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

    // Load data of translatable properties.
    $this->attachPropertyData($queried_entities);
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
   * Overrides Drupal\entity\DatabaseStorageController::attachLoad().
   *
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
      $queried_entities[$id]->user[$langcode][0]['id'] = $values['uid'];
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
      // Add values for all properties.
      $property_values = array();
      $fields = field_info_fields();

      foreach ($record as $name => $value) {
        switch ($name) {

          // Translatable property values are already mapped,
          // see attachPropertyData().
          case 'name':
          case 'user':
            $property_values[$name] = $value;
            break;

          case 'langcode':
            $property_values['language'][LANGUAGE_NOT_SPECIFIED][0]['langcode'] = $value;
            break;

          default:
            if (isset($fields[$name]) && $value) {
              // Handle per-language values of fields.
              $property_values[$name] = $value;
            }
            // Else assume a not translatable property with a single value.
            else {
              $property_values[$name][LANGUAGE_NOT_SPECIFIED][0]['value'] = $value;
            }
            break;
        }
      }

      // Pass the plain property values in during entity construction.
      $values['values'] = $property_values;
      $entity = new $this->entityClass($values, $this->entityType);

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

      if (!$entity->isNew()) {
        $return = drupal_write_record($this->entityInfo['base table'], $record, 'id');
        $this->resetCache(array($entity->{$this->idKey}));
        $this->postSave($entity, TRUE, $record);
        $this->invokeHook('update', $entity, $record);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base table'], $record);
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->{$this->idKey}->value = $record->id;
        $entity->enforceIsNew(FALSE);
        $this->postSave($entity, FALSE, $record);
        $this->invokeHook('insert', $entity, $record);
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
   * Make sure to pass on mapped storage records to field API attachers for
   * saving.
   */
  protected function invokeHook($hook, EntityInterface $entity, \stdclass $record = NULL) {
    if (!empty($this->entityInfo['fieldable']) && function_exists($function = 'field_attach_' . $hook)) {
      $function($this->entityType, $record ? $record : $entity);
    }
    // Invoke the hook.
    module_invoke_all($this->entityType . '_' . $hook, $entity);
    // Invoke the respective entity-level hook.
    module_invoke_all('entity_' . $hook, $entity, $this->entityType);
  }

  /**
   * Maps from entity objects to storage records.
   *
   * @param $langcode
   *   (optional) If set, get the values of the translation.
   */
  protected function mapToStorageRecord(EntityInterface $entity) {
    $record = new \stdClass();

    $langcodes = array_keys($entity->translations());
    // Values in default language are keyed by LANGUAGE_NOT_SPECIFIED.
    $langcodes[] = LANGUAGE_NOT_SPECIFIED;

    foreach ($langcodes as $langcode) {
      foreach ($entity->getTranslation($langcode) as $name => $property) {
        switch ($name) {

          // Translatable properties.
          case 'name':
            $record->property_data[$langcode]['name'] = $property->value;
            break;
          case 'user':
            $record->property_data[$langcode]['uid'] = $property->id;
            break;

          // Not translatable properties and fields.
          case 'language':
            $record->langcode = $property->langcode;
            break;

          default:
            $definition = $property->getDefinition();
            // @todo: Support all languages here, e.g. by getting all properties
            // that have been changed for each language.

            if (!empty($definition['field'])) {
              $record->{$name}[$langcode] = $property->toArray();
            }
            else {
              // Just get the value of the first item.
              $record->$name = $property->value;
            }
            break;
        }
      }
    }
    return $record;
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   */
  protected function postSave(EntityInterface $entity, $update, $record = NULL) {

    foreach ($record->property_data as $langcode => $values) {
      $values = array(
        'id' => $entity->id(),
        'langcode' => LANGUAGE_NOT_SPECIFIED == $langcode ? $entity->language->langcode : $langcode,
        'default_langcode' => intval(LANGUAGE_NOT_SPECIFIED == $langcode),
      ) + $values;

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
    $properties['language'] = array(
      'label' => t('Language'),
      'description' => ('The language of the test entity.'),
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
    $properties['user'] = array(
      'label' => t('User'),
      'description' => t('The associated user.'),
      'type' => 'entityreference_item',
      'entity type' => 'user',
      'list' => TRUE,
      'translatable' => TRUE,
    );
    return $properties;
  }
}
