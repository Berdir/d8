<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Core\Entity\FieldInstance.
 */

namespace Drupal\field\Plugin\Core\Entity;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\field\FieldException;

/**
 * Defines the Field instance entity.
 *
 * @Plugin(
 *   id = "field_instance",
 *   label = @Translation("Field instance"),
 *   module = "field",
 *   controller_class = "Drupal\field\FieldInstanceStorageController",
 *   config_prefix = "field.instance",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class FieldInstance extends ConfigEntityBase implements \ArrayAccess {

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
   * The instance field id.
   *
   * @var string
   */
  public $field_id;

  /**
   * The instance entity type.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The instance bundle.
   *
   * @var string
   */
  public $bundle;

  /**
   * The instance label.
   *
   * @var string
   */
  public $label;

  /**
   * Default value.
   *
   * @var array
   */
  public $default_value;

  /**
   * Default value function
   *
   * @var string
   */
  public $default_value_function;

  /**
   * The instance settings.
   *
   * @var array
   */
  public $settings;

  /**
   * The instance widget settings.
   *
   * @var array
   */
  public $widget;

  /**
   * The instance description.
   *
   * @var string
   */
  public $description;

  /**
   * The instance deleted state.
   *
   * @var bool
   */
  public $deleted;

  /**
   * The instance required state.
   *
   * @var bool
   */
  public $required;

  /**
   * The widget plugin used for this instance.
   *
   * @var \Drupal\field\Plugin\Type\Widget\WidgetInterface
   */
  protected $widgetPlugin;

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityBase::__construct().
   */
  public function __construct(array $values, $entity_type) {
    // Check required properties.
    if (empty($values['field_name'])) {
      throw new FieldException('Attempt to create an instance of an unspecified field.');
    }
    if (empty($values['entity_type'])) {
      throw new FieldException(format_string('Attempt to create an instance of field @field_name without an entity type.', array('@field_name' => $values['field_name'])));
    }
    if (empty($values['bundle'])) {
      throw new FieldException(format_string('Attempt to create an instance of field @field_name without a bundle.', array('@field_name' => $values['field_name'])));
    }

    // Provide defaults.
    $values += array(
      'label' => $values['field_name'],
      'description' => '',
      'required' => FALSE,
      'default_value' => array(),
      'default_value_function' => '',
      'settings' => array(),
      'widget' => array(),
      'deleted' => 0,
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
      'field_id',
      'entity_type',
      'bundle',
      'label',
      'description',
      'required',
      'default_value',
      'default_value_function',
      'settings',
      'widget',
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
    $field = field_read_field($this->field_name);

    if ($this->isNew()) {
      if (empty($field)) {
        throw new FieldException(format_string("Attempt to save an instance of a field @field_name that doesn't exist or is currently inactive.", array('@field_name' => $this->field_name)));
      }
      // Check that the field can be attached to this entity type.
      if (!empty($field->entity_types) && !in_array($this->entity_type, $field->entity_types)) {
        throw new FieldException(format_string('Attempt to create an instance of field @field_name on forbidden entity type @entity_type.', array('@field_name' => $this->field_name, '@entity_type' => $this->entity_type)));
      }

      // Ensure the field instance is unique within the bundle. We only check
      // for instances of active fields, since adding an instance of a disabled
      // field is not supported.
      if (field_read_instance($this->entity_type, $this->field_name, $this->bundle)) {
        throw new FieldException(format_string('Attempt to create an instance of field @field_name on bundle @bundle that already has an instance of that field.', array('@field_name' => $this->field_name, '@bundle' => $this->bundle)));
      }

      // Set the field uuid.
      $this->field_id = $field->uuid;

      // Assign the id.
      $this->id = $this->entity_type . '.' . $this->bundle . '.' . $this->field_name;

      $hook = 'field_create_instance';
      $hook_args = array($this);
    }
    else {
      $original = \Drupal::service('plugin.manager.entity')
        ->getStorageController($this->entityType)
        ->loadUnchanged($this->id());

      // Some updates are always disallowed.
      if ($this->entity_type != $original->entity_type) {
        throw new FieldException("Cannot change an existing instance's entity_type.");
      }
      if ($this->bundle != $original->bundle) {
        throw new FieldException("Cannot change an existing instance's bundle.");
      }
      if ($this->field_name != $original->field_name || $this->field_id != $original->field_id) {
        throw new FieldException("Cannot change an existing instance's field.");
      }

      $hook = 'field_update_instance';
      $hook_args = array($this, $original);
    }

    $field_type_info = field_info_field_types($field->type);

    // Set default instance settings.
    $this->settings += $field_type_info['instance_settings'];

    // Set default widget and settings.
    $this->widget += array(
      'type' => $field_type_info['default_widget'],
      'settings' => array(),
    );
    // Check widget module.
    if ($widget_type_info = \Drupal::service('plugin.manager.field.widget')->getDefinition($this->widget['type'])) {
      $this->widget['module'] = $widget_type_info['module'];
      $this->widget['settings'] += $widget_type_info['settings'];
    }
    // If no weight specified, make sure the field sinks at the bottom.
    if (!isset($this->widget['weight'])) {
      $max_weight = field_info_max_weight($this->entity_type, $this->bundle, 'form');
      $this->widget['weight'] = isset($max_weight) ? $max_weight + 1 : 0;
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
   *
   * @param $field_cleanup
   *   (Optionnal) If TRUE, the field will be deleted as well if its last
   *   instance is being deleted. If FALSE, it is the caller's responsibility to
   *   handle the case of fields left without instances. Defaults to TRUE.
   */
  public function delete($field_cleanup = TRUE) {
    if (!$this->deleted) {
      $module_handler = \Drupal::service('module_handler');

      // Delete the configuration of this instance and save the configuration
      // in the key_value table so we can use it later during
      // field_purge_batch().
      $deleted_instances = state()->get('field.instance.deleted') ?: array();
      $this->deleted = TRUE;
      $deleted_instances[$this->uuid] = $this;
      state()->set('field.instance.deleted', $deleted_instances);

      parent::delete();

      // Clear the cache.
      field_cache_clear();

      // Mark instance data for deletion.
      $field = field_info_field($this->field_name);
      $module_handler->invoke($field->storage['module'], 'field_storage_delete_instance', array($this));

      // Let modules react to the deletion of the instance.
      $module_handler->invokeAll('field_delete_instance', array($this));

      // Delete the field itself if we just deleted its last instance.
      if ($field_cleanup && count($field->getBundles()) == 0) {
        $field->delete();
      }
    }
  }

  /**
   * Returns the Widget plugin for the instance.
   *
   * @return Drupal\field\Plugin\Type\Widget\WidgetInterface
   *   The Widget plugin to be used for the instance.
   */
  public function getWidget() {
    if (empty($this->widgetPlugin)) {
      $widget_properties = $this->widget;

      // Let modules alter the widget properties.
      $context = array(
        'entity_type' => $this->entity_type,
        'bundle' => $this->bundle,
        'field' => field_info_field($this->field_name),
        'instance' => $this,
      );
      drupal_alter(array('field_widget_properties', 'field_widget_properties_' . $this->entity_type), $widget_properties, $context);

      $options = array(
        'instance' => $this,
        'type' => $widget_properties['type'],
        'settings' => $widget_properties['settings'],
        'weight' => $widget_properties['weight'],
      );
      $this->widgetPlugin = \Drupal::service('plugin.manager.field.widget')->getInstance($options);
    }

    return $this->widgetPlugin;
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return isset($this->{$offset});
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function &offsetGet($offset) {
    return $this->{$offset};
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    $this->{$offset} = $value;
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    unset($this->{$offset});
  }

}

