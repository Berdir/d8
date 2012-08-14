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
   * An array of property information, i.e. containing definitions.
   *
   * @var array
   */
  protected $propertyInfo;


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
    $entity = new $this->entityClass(array(), $this->entityType);

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
        $this->invokeHook('update', $entity, $record);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base table'], $record);
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->{$this->idKey}->value = $record->id;
        $entity->enforceIsNew(FALSE);
        $this->postSave($entity, FALSE);
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
   * Maps from storage records to entity objects.
   *
   * @return array
   *   An array of entity objects implementing the EntityInterface.
   */
  protected function mapFromStorageRecords(array $records) {

    foreach ($records as $id => $record) {
      // Compile values of everything that is no property yet.
      $values['langcode'] = $record->langcode;

      // Add values for all properties.
      $property_values = array();
      $langcode = $record->langcode;
      $fields = field_info_fields();

      foreach ($record as $name => $value) {
        switch ($name) {
          case 'uid':
            $property_values['user'][$langcode][0]['id'] = $value;
            break;

          default:
            if (isset($fields[$name]) && $value) {
              // Handle per-language values of fields.
              $property_values[$name] = $value;
            }
            elseif (!isset($values[$name])) {
              $property_values[$name][$langcode][0]['value'] = $value;
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
   * Maps from storage records to entity objects.
   */
  protected function mapToStorageRecord(EntityInterface $entity) {
    $record = new \stdClass();
    $record->langcode = $entity->langcode;

    foreach ($entity as $name => $property) {
      switch ($name) {
        case 'user':
          $record->uid = $entity->user->id;
          break;
        default:
          $definition = $property->getDefinition();
          // @todo: Support all languages here, e.g. by getting all properties
          // that have been changed for each language.

          if (!empty($definition['field'])) {
            $record->{$name}[LANGUAGE_NOT_SPECIFIED] = $property->toArray();
          }
          else {
            // Just get the first value of the first item, e.g.
            // $entity->name[0]->value.
            $record->$name = current($property[0]->toArray());
          }
          break;
      }
    }
    return $record;
  }

  /**
   * Gets an array property definitions for the entity's properties.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   *
   * @todo Add to interface.
   */
  public function getPropertyDefinitions(array $definition) {
    // @todo: Add caching for $this->propertyInfo.
    if (!isset($this->propertyInfo)) {
      $this->propertyInfo = array(
        'definitions' => $this->basePropertyDefinitions(),
        // Contains definitions of optional (per-bundle) properties.
        'optional' => array(),
        // An array keyed by bundle name containing the names of the per-bundle
        // properties.
        'bundle map' => array(),
      );

      // Invoke hooks.
      $result = module_invoke_all($this->entityType . '_property_info');
      $this->propertyInfo = array_merge_recursive($this->propertyInfo, $result);
      $result = module_invoke_all('entity_property_info', $this->entityType);
      $this->propertyInfo = array_merge_recursive($this->propertyInfo, $result);

      $hooks = array('entity_property_info', $this->entityType . '_property_info');
      drupal_alter($hooks, $this->propertyInfo, $this->entityType);
    }

    $definitions = $this->propertyInfo['definitions'];

    // Add in per-bundle properties.
    // @todo: Should this be statically cached as well?
    if (!empty($definition['bundle']) && isset($this->propertyInfo['bundle map'][$definition['bundle']])) {
      $definitions += array_intersect_key($this->propertyInfo['optional'], array_flip($this->propertyInfo['bundle map'][$definition['bundle']]));
    }

    return $definitions;
  }

  /**
   * Implements \Drupal\entity\EntityStorageControllerInterface.
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
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'string_item',
      'list' => TRUE,
    );
    $properties['user'] = array(
      'label' => t('User'),
      'description' => t('The associated user.'),
      'type' => 'entityreference_item',
      'entity type' => 'user',
      'list' => TRUE,
    );
    return $properties;
  }
}
