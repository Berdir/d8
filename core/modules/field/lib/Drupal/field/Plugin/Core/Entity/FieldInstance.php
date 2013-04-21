<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Core\Entity\FieldInstance.
 */

namespace Drupal\field\Plugin\Core\Entity;

use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\field\FieldException;

/**
 * Defines the Field instance entity.
 *
 * @EntityType(
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
   * The instance ID (machine name).
   *
   * The ID consists of 3 parts: the entity type, bundle and the field name.
   *
   * Example: node.article.body, user.user.field_main_image.
   *
   * @var string
   */
  public $id;

  /**
   * The instance UUID.
   *
   * This is assigned automatically when the instance is created.
   *
   * @var string
   */
  public $uuid;

  /**
   * The UUID of the field attached to the bundle by this instance.
   *
   * @var string
   */
  public $field_uuid;

  /**
   * The name of the field attached to the bundle by this instance.
   *
   * @var string
   *
   * @todo Revisit that in favor of a getField() method.
   *   See http://drupal.org/node/1967106.
   * @todo This variable is provided for backward compatibility and will be
   *   removed.
   */
  public $field_name;

  /**
   * The name of the entity type the instance is attached to.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The name of the bundle the instance is attached to.
   *
   * @var string
   */
  public $bundle;

  /**
   * The human-readable label for the instance.
   *
   * This will be used as the title of Form API elements for the field in entity
   * edit forms, or as the label for the field values in displayed entities.
   *
   * @var string
   */
  public $label;

  /**
   * The instance description.
   *
   * A human-readable description for the field when used with this bundle.
   * For example, the description will be the help text of Form API elements for
   * this instance in entity edit forms.
   *
   * @var string
   */
  public $description;

  /**
   * Field-type specific settings.
   *
   * An array of key/value pairs. The keys and default values are defined by the
   * field type in the 'instance_settings' entry of hook_field_info().
   *
   * @var array
   */
  public $settings;

  /**
   * Flag indicating whether the field is required.
   *
   * TRUE if a value for this field is required when used with this bundle,
   * FALSE otherwise. Currently, required-ness is only enforced at the Form API
   * level in entity edit forms, not during direct API saves.
   *
   * @var bool
   */
  public $required;

  /**
   * Default field value.
   *
   * The default value is used when an entity is created, either:
   * - through an entity creation form; the form elements for the field are
   *   prepopulated with the default value.
   * - through direct API calls (i.e. $entity->save()); the default value is
   *   added if the $entity object provides no explicit entry (actual values or
   *   "the field is empty") for the field.
   *
   * The default value is expressed as a numerically indexed array of items,
   * each item being an array of key/value pairs matching the set of 'columns'
   * defined by the "field schema" for the field type, as exposed in
   * hook_field_schema(). If the number of items exceeds the cardinality of the
   * field, extraneous items will be ignored.
   *
   * This property is overlooked if the $default_value_function is non-empty.
   *
   * Example for a number_integer field:
   * @code
   * array(
   *   array('value' => 1),
   *   array('value' => 2),
   * )
   * @endcode
   *
   * @var array
   */
  public $default_value;

  /**
   * The name of a callback function that returns default values.
   *
   * The function will be called with the following arguments:
   * - \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being created.
   * - \Drupal\field\Plugin\Core\Entity\Field $field
   *   The field object.
   * - \Drupal\field\Plugin\Core\Entity\FieldInstance $instance
   *   The field instance object.
   * - string $langcode
   *   The language of the entity being created.
   * It should return an array of default values, in the same format as the
   * $default_value property.
   *
   * This property takes precedence on the list of fixed values specified in the
   * $default_value property.
   *
   * @var string
   */
  public $default_value_function;

  /**
   * The widget definition.
   *
   * An array of key/value pairs identifying the Form API input widget for
   * the field when used by this bundle.
   *   - type: (string) The plugin ID of the widget, such as text_textfield.
   *   - settings: (array) A sub-array of key/value pairs of settings. The keys
   *     and default values are defined by the widget plugin in the 'settings'
   *     entry of its "plugin definition" (typycally plugin class annotations).
   *   - weight: (float) The weight of the widget relative to the other
   *     elements in entity edit forms.
   *   - module: (string, read-only) The name of the module that provides the
   *     widget plugin.
   *
   * @var array
   */
  public $widget;

  /**
   * Flag indicating whether the instance is deleted.
   *
   * The delete() method marks the instance as "deleted" and removes the
   * corresponding entry from the config storage, but keeps its definition in
   * the state storage while field data is purged by a separate
   * garbage-collection process.
   *
   * Deleted instances stay out of the regular entity lifecycle (notably, their
   * values are not populated in loaded entities, and are not saved back).
   *
   * @var bool
   */
  public $deleted;

  /**
   * The widget plugin used for this instance.
   *
   * @var \Drupal\field\Plugin\Type\Widget\WidgetInterface
   */
  protected $widgetPlugin;

  /**
   * Flag indicating whether the bundle name can be renamed or not.
   *
   * @var bool
   */
  protected $bundle_rename_allowed = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type = 'field_instance') {
    // Check required properties.
    if (empty($values['entity_type'])) {
      throw new FieldException(format_string('Attempt to create an instance of field @field_name without an entity type.', array('@field_name' => $values['field_name'])));
    }
    if (empty($values['bundle'])) {
      throw new FieldException(format_string('Attempt to create an instance of field @field_name without a bundle.', array('@field_name' => $values['field_name'])));
    }

    // Accept incoming 'field_name' instead of 'field_uuid', for easier DX on
    // creation of new instances.
    if (isset($values['field_name']) && !isset($values['field_uuid'])) {
      $field = field_info_field($values['field_name']);
      if ($field) {
        $values['field_uuid'] = $field->uuid;
      }
      else {
        throw new FieldException(format_string('Attempt to create an instance of unknown, disabled, or deleted field @name', array('@name' => $values['field_name'])));
      }
    }
    // Fill in the field_name property for data coming out of config.
    // @todo Revisit that in favor of a getField() method.
    //   See http://drupal.org/node/1967106.
    elseif (isset($values['field_uuid']) && !isset($values['field_name'])) {
      $field = current(field_read_fields(array('uuid' => $values['field_uuid']), array('include_inactive' => TRUE, 'include_deleted' => TRUE)));
      if ($field) {
        $values['field_name'] = $field->id;
      }
      else {
        throw new FieldException(format_string('Attempt to create an instance of unknown field @uuid', array('@uuid' => $values['field_uuid'])));
      }
    }

    if (empty($values['field_uuid'])) {
      throw new FieldException('Attempt to create an instance of an unspecified field.');
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
      'deleted' => FALSE,
    );
    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->entity_type . '.' . $this->bundle . '.' . $this->field_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getExportProperties() {
    $names = array(
      'id',
      'uuid',
      'status',
      'langcode',
      'field_uuid',
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
   * {@inheritdoc}
   */
  public function save() {
    $module_handler = \Drupal::moduleHandler();
    $entity_manager = \Drupal::service('plugin.manager.entity');
    $field_controller = $entity_manager->getStorageController('field_entity');
    $instance_controller = $entity_manager->getStorageController($this->entityType);

    $field = current($field_controller->load(array($this->field_name)));

    if ($this->isNew()) {
      if (empty($field)) {
        throw new FieldException(format_string("Attempt to save an instance of a field @field_id that doesn't exist or is currently inactive.", array('@field_name' => $this->field_name)));
      }
      // Check that the field can be attached to this entity type.
      if (!empty($field->entity_types) && !in_array($this->entity_type, $field->entity_types)) {
        throw new FieldException(format_string('Attempt to create an instance of field @field_name on forbidden entity type @entity_type.', array('@field_name' => $this->field_name, '@entity_type' => $this->entity_type)));
      }

      // Assign the ID.
      $this->id = $this->id();

      // Ensure the field instance is unique within the bundle.
      if ($prior_instance = current($instance_controller->load(array($this->id)))) {
        throw new FieldException(format_string('Attempt to create an instance of field @field_name on bundle @bundle that already has an instance of that field.', array('@field_name' => $this->field_name, '@bundle' => $this->bundle)));
      }

      // Set the field UUID.
      $this->field_uuid = $field->uuid;

      $hook = 'field_create_instance';
      $hook_args = array($this);
    }
    // Otherwise, the field instance is being updated.
    else {
      $original = \Drupal::service('plugin.manager.entity')
        ->getStorageController($this->entityType)
        ->loadUnchanged($this->getOriginalID());

      // Some updates are always disallowed.
      if ($this->entity_type != $original->entity_type) {
        throw new FieldException("Cannot change an existing instance's entity_type.");
      }
      if ($this->bundle != $original->bundle && empty($this->bundle_rename_allowed)) {
        throw new FieldException("Cannot change an existing instance's bundle.");
      }
      if ($this->field_name != $original->field_name || $this->field_uuid != $original->field_uuid) {
        throw new FieldException("Cannot change an existing instance's field.");
      }

      $hook = 'field_update_instance';
      $hook_args = array($this, $original);
    }

    $field_type_info = field_info_field_types($field->type);

    // Set the default instance settings.
    $this->settings += $field_type_info['instance_settings'];

    // Set the default widget and settings.
    $this->widget += array(
      'type' => $field_type_info['default_widget'],
      'settings' => array(),
    );
    // Get the widget module and settings from the widget type.
    if ($widget_type_info = \Drupal::service('plugin.manager.field.widget')->getDefinition($this->widget['type'])) {
      $this->widget['module'] = $widget_type_info['module'];
      $this->widget['settings'] += $widget_type_info['settings'];
    }
    // If no weight is specified, make sure the field sinks to the bottom.
    if (!isset($this->widget['weight'])) {
      $max_weight = field_info_max_weight($this->entity_type, $this->bundle, 'form');
      $this->widget['weight'] = isset($max_weight) ? $max_weight + 1 : 0;
    }

    // Save the configuration.
    $result = parent::save();
    field_cache_clear();

    // Invoke external hooks after the cache is cleared for API consistency.
    // This invokes hook_field_create_instance() or hook_field_update_instance()
    // depending on whether the field is new.
    $module_handler->invokeAll($hook, $hook_args);

    return $result;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::delete().
   *
   * @param bool $field_cleanup
   *   (optional) If TRUE, the field will be deleted as well if its last
   *   instance is being deleted. If FALSE, it is the caller's responsibility to
   *   handle the case of fields left without instances. Defaults to TRUE.
   */
  public function delete($field_cleanup = TRUE) {
    if (!$this->deleted) {
      $module_handler = \Drupal::moduleHandler();
      $state = \Drupal::state();

      // Delete the configuration of this instance and save the configuration
      // in the key_value table so we can use it later during
      // field_purge_batch().
      $deleted_instances = $state->get('field.instance.deleted') ?: array();
      $config = $this->getExportProperties();
      $config['deleted'] = TRUE;
      $deleted_instances[$this->uuid] = $config;
      $state->set('field.instance.deleted', $deleted_instances);

      parent::delete();

      // Clear the cache.
      field_cache_clear();

      // Mark instance data for deletion by invoking
      // hook_field_storage_delete_instance().
      $field = field_info_field($this->field_name);
      $module_handler->invoke($field->storage['module'], 'field_storage_delete_instance', array($this));

      // Let modules react to the deletion of the instance with
      // hook_field_delete_instance().
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
        'field' => field_info_field_by_id($this->field_uuid),
        'instance' => $this,
      );
      // Invoke hook_field_widget_properties_alter() and
      // hook_field_widget_properties_ENTITY_TYPE_alter().
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
   * Allows a bundle to be renamed.
   *
   * Renaming a bundle on the instance is allowed when an entity's bundle
   * is renamed and when field_entity_bundle_rename() does internal
   * housekeeping.
   */
  public function allowBundleRename() {
    $this->bundle_rename_allowed = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return (isset($this->{$offset}) || $offset == 'field_id');
  }

  /**
   * {@inheritdoc}
   */
  public function &offsetGet($offset) {
    if ($offset == 'field_id') {
      return $this->field_uuid;
    }
    return $this->{$offset};
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    if ($offset == 'field_id') {
      $offset = 'field_uuid';
    }
    $this->{$offset} = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    if ($offset == 'field_id') {
      $offset = 'field_uuid';
    }
    unset($this->{$offset});
  }

}

