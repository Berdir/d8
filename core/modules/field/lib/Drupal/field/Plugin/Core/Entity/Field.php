<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Core\Entity\Field.
 */

namespace Drupal\field\Plugin\Core\Entity;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\field\FieldException;

/**
 * Defines the Field entity.
 *
 * @todo use 'field' as the id once hook_field_load() and friends
 * are removed.
 *
 * @Plugin(
 *   id = "field_entity",
 *   label = @Translation("Field"),
 *   module = "field",
 *   controller_class = "Drupal\field\FieldStorageController",
 *   config_prefix = "field.field",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Field extends ConfigEntityBase implements \ArrayAccess {

  /**
   * The field ID (config name).
   *
   * @var string
   */
  public $id;

  /**
   * The field UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The field name.
   *
   * @var string
   */
  public $field_name;

  /**
   * The field type.
   *
   * @var string
   */
  public $type;

  /**
   * The field module.
   *
   * @var string
   */
  public $module;

  /**
   * The field active state.
   *
   * @var bool
   */
  public $active;

  /**
   * The field storage locked state.
   *
   * @var bool
   */
  public $locked;

  /**
   * The field cardinality.
   *
   * @var integer
   */
  public $cardinality;

  /**
   * The field translatable state.
   *
   * @var bool
   */
  public $translatable;

  /**
   * The field deleted state.
   *
   * @var bool
   */
  public $deleted;

  /**
   * The field settings.
   *
   * @var array
   */
  public $settings;

  /**
   * The field entity types.
   *
   * @var array
   */
  public $entity_types;

  /**
   * The field indexes.
   *
   * @var array
   */
  public $indexes;

  /**
   * The field storage.
   *
   * @var array
   */
  public $storage;

  /**
   * The field schema.
   *
   * @var array
   */
  protected $schema;

  /**
   * The storage information for the field.
   *
   * @var array
   */
  protected $storageDetails;

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityBase::__construct().
   */
  public function __construct(array $values, $entity_type) {
    // Check required properties.
    if (empty($values['type'])) {
      throw new FieldException('Attempt to create a field with no type.');
    }
    if (empty($values['field_name'])) {
      throw new FieldException('Attempt to create an unnamed field.');
    }
    if (!preg_match('/^[_a-z]+[_a-z0-9]*$/', $values['field_name'])) {
      throw new FieldException('Attempt to create a field with invalid characters. Only lowercase alphanumeric characters and underscores are allowed, and only lowercase letters and underscore are allowed as the first character');
    }

    $this->id = $values['field_name'];

    // Provide defaults.
    $values += array(
      'settings' => array(),
      'cardinality' => 1,
      'translatable' => FALSE,
      'entity_types' => array(),
      'locked' => FALSE,
      'deleted' => 0,
      'storage' => array(),
      'indexes' => array(),
    );
    parent::__construct($values, $entity_type);
  }

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityBase::getExportProperties().
   */
  public function getExportProperties() {
    $names = array(
      'id',
      'uuid',
      'status',
      'langcode',
      'field_name',
      'type',
      'settings',
      'module',
      'active',
      'entity_types',
      'storage',
      'locked',
      'cardinality',
      'translatable',
      'indexes',
    );
    $properties = array();
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::save().
   */
  public function save() {
    $module_handler = \Drupal::service('module_handler');

    // Clear the derived data about the field.
    unset($this->schema, $this->storageDetails);

    if ($this->isNew()) {
      // Field name cannot be longer than 32 characters. We use drupal_strlen()
      // because the DB layer assumes that column widths are given in characters,
      // not bytes.
      if (drupal_strlen($this->field_name) > 32) {
        throw new FieldException(t('Attempt to create a field with a name longer than 32 characters: %name',
          array('%name' => $this->field_name)));
      }

      // Ensure the field name is unique over active and disabled fields.
      // We do not care about deleted fields.
      $prior_field = field_read_field($this->field_name, array('include_inactive' => TRUE));
      if (!empty($prior_field)) {
        $message = $prior_field->active ?
          t('Attempt to create field name %name which already exists and is active.', array('%name' => $this->field_name)):
          t('Attempt to create field name %name which already exists, although it is inactive.', array('%name' => $this->field_name));
        throw new FieldException($message);
      }

      // Disallow reserved field names. This can't prevent all field name
      // collisions with existing entity properties, but some is better
      // than none.
      foreach (\Drupal::service('plugin.manager.entity')->getDefinitions() as $type => $info) {
        if (in_array($this->field_name, $info['entity_keys'])) {
          throw new FieldException(t('Attempt to create field name %name which is reserved by entity type %type.', array('%name' => $this->field_name, '%type' => $type)));
        }
      }

      // Check that the field type is known.
      $field_type = field_info_field_types($this->type);
      if (!$field_type) {
        throw new FieldException(t('Attempt to create a field of unknown type %type.', array('%type' => $this->type)));
      }
      $this->module = $field_type['module'];
      $this->active = 1;

      // Create all per-field-type properties (needed here as long as we have
      // settings that impact column definitions).
      $this->settings += $field_type['settings'];

      // Provide default storage.
      $this->storage += array(
        'type' => variable_get('field_storage_default', 'field_sql_storage'),
        'settings' => array(),
      );
      // Check that the storage type is known.
      $storage_type = field_info_storage_types($this->storage['type']);
      if (!$storage_type) {
        throw new FieldException(t('Attempt to create a field with unknown storage type %type.', array('%type' => $this->storage['type'])));
      }
      $this->storage['module'] = $storage_type['module'];
      $this->storage['active'] = 1;
      // Provide default storage settings.
      $this->storage['settings'] += $storage_type['settings'];

      // Notify the storage backend,
      $module_handler->invoke($this->storage['module'], 'field_storage_create_field', array($this));

      $hook = 'field_create_field';
      $hook_args = array($this);
    }
    else {
      $original = \Drupal::service('plugin.manager.entity')
        ->getStorageController($this->entityType)
        ->loadUnchanged($this->id());

      // Some updates are always disallowed.
      if ($this->type != $original->type) {
        throw new FieldException("Cannot change an existing field's type.");
      }
      if ($this->entity_types != $original->entity_types) {
        throw new FieldException("Cannot change an existing field's entity_types property.");
      }
      if ($this->storage['type'] != $original->storage['type']) {
        throw new FieldException("Cannot change an existing field's storage type.");
      }

      // Make sure all settings are present.
      $this->settings += $original->settings;

      $has_data = field_has_data($this);

      // See if any module forbids the update by throwing an exception.
      $module_handler->invokeAll('field_update_forbid', array($this, $original, $has_data));

      // Tell the storage engine to update the field. Do this before saving the
      // new definition since it still might fail.
      $module_handler->invoke($this->storage['module'], 'field_storage_update_field', array($this, $original, $has_data));

      $hook = 'field_update_field';
      $hook_args = array($this, $original, $has_data);
    }

    // Save the configuration.
    $result = parent::save();
    field_cache_clear();

    // Invoke external hooks after the cache is cleared for API consistency.
    $module_handler->invokeAll($hook, $hook_args);

    return $result;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::delete().
   */
  public function delete() {
    if (!$this->deleted) {
      $module_handler = \Drupal::service('module_handler');

      // Delete all non-deleted instances.
      foreach ($this->getBundles() as $entity_type => $bundles) {
        foreach ($bundles as $bundle) {
          // We need to get inactive instances too (e.g. the entity type is not
          // known anymore - case of comment.module being uninstalled).
          $instance = field_read_instance($entity_type, $this->field_name, $bundle, array('include_inactive' => TRUE));
          field_delete_instance($instance, FALSE);
        }
      }

      // Mark field data for deletion.
      $module_handler->invoke($this->storage['module'], 'field_storage_delete_field', array($this));

      // Delete the configuration of this field and save the field configuration
      // in the key_value table so we can use it later during field_purge_batch().
      // This makes sure a new field can be created immediately with the same
      // name.
      $deleted_fields = state()->get('field.field.deleted') ?: array();
      $this->deleted = TRUE;
      // @todo we should save the raw CMI data, not the entity.
      $deleted_fields[$this->uuid] = $this;
      state()->set('field.field.deleted', $deleted_fields);

      parent::delete();

      // Clear the cache.
      field_cache_clear();

      $module_handler->invokeAll('field_delete_field', array($this));
    }
  }

  /**
   * Returns the field schema.
   *
   * @return array
   *   The field schema, as defined by hook_field_schema().
   */
  public function getSchema() {
    if (!isset($this->schema)) {
      $module_handler = \Drupal::service('module_handler');
      // @todo Use $module_handler->loadInclude() once
      // http://drupal.org/node/1941000 is fixed.
      module_load_install($this->module);
      $schema = (array) $module_handler->invoke($this->module, 'field_schema', array($this));
      $schema += array('columns' => array(), 'indexes' => array(), 'foreign keys' => array());
      $schema['indexes'] = $this->indexes + $schema['indexes'];
      if (array_intersect(array_keys($schema['columns']), field_reserved_columns())) {
        throw new FieldException(t('Illegal field type columns.'));
      }
      $this->schema = $schema;
    }
    return $this->schema;
  }

  /**
   * Returns the storage details for the field.
   *
   * @return array
   *   The storage details. @todo document.
   */
  public function getStorageDetails() {
    if (!isset($this->storageDetails)) {
      $module_handler = \Drupal::service('module_handler');
      $details = (array) $module_handler->invoke($this->storage['module'], 'field_storage_details', array($this));
      $module_handler->alter('field_storage_details', $details, $this);
      $this->storageDetails = $details;
    }
    return $this->storageDetails;
  }

  /**
   * Returns the list of bundles where the field has instances.
   *
   * @return array
   *   An array keyed by entity type names, whose values are arrays of bundle
   *   names.
   */
  public function getBundles() {
    if (empty($this->deleted)) {
      $map = field_info_field_map();
      if (isset($map[$this->field_name]['bundles'])) {
        return $map[$this->field_name]['bundles'];
      }
    }
    return array();
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return isset($this->{$offset}) || in_array($offset, array('columns', 'foreign keys', 'bundles', 'storage details'));
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function &offsetGet($offset) {
    switch ($offset) {
      case 'columns':
        $this->getSchema();
        return $this->schema['columns'];

      case 'foreign keys':
        $this->getSchema();
        return $this->schema['foreign keys'];

      case 'bundles':
        $bundles = $this->getBundles();
        return $bundles;

      case 'storage details':
        $this->getStorageDetails();
        return $this->storageDetails;
    }

    return $this->{$offset};
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    if (!in_array($offset, array('columns', 'foreign keys', 'bundles', 'storage details'))) {
      $this->{$offset} = $value;
    }
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    if (!in_array($offset, array('columns', 'foreign keys', 'bundles', 'storage details'))) {
      unset($this->{$offset});
    }
  }

}
