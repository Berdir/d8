<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Type\ConfigurableEntityReferenceItem.
 */

namespace Drupal\entity_reference\Type;

use Drupal\Core\Entity\Field\Type\EntityReferenceItem;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field\Plugin\Type\FieldType\CFieldItemInterface;
use Drupal\field\Plugin\Core\Entity\Field;
use Drupal\field\Field as FieldAPI;

/**
 * Defines the 'entity_reference_configurable' entity field item.
 *
 * Extends the Core 'entity_reference' entity field item with properties for
 * revision ids, labels (for autocreate) and access.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - target_type: The entity type to reference.
 */
class ConfigurableEntityReferenceItem extends EntityReferenceItem implements CFieldItemInterface {

  /**
   * Definitions of the contained properties.
   *
   * @see ConfigurableEntityReferenceItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * The Field instance definition.
   *
   * @var \Drupal\field\Plugin\Core\Entity\FieldInstance
   */
  protected $instance;

  /**
   * Constructs a Drupal\Component\Plugin\ConfigurableEntityReferenceItem object.
   *
   * Duplicated from \Drupal\field\Plugin\Type\FieldType\CFieldItemBase, since
   * we cannot extend it.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\field\Plugin\Core\Entity\Field $field
   *   The field definition.
   */
  public function __construct(array $definition, $plugin_id, array $plugin_definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $plugin_id, $plugin_definition, $name, $parent);
    // @todo No good, the instance must be injected somehow.
    $entity = $parent->getParent();
    $instances = FieldAPI::fieldInfo()->getBundleInstances($entity->entityType(), $entity->bundle());
    $this->instance = $instances[$parent->name];
  }

  /**
   * Overrides \Drupal\Core\Entity\Field\Type\EntityReferenceItem::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Definitions vary by entity type, so key them by entity type.
    $target_type = $this->definition['settings']['target_type'];

    if (!isset(self::$propertyDefinitions[$target_type])) {
      // Call the parent to define the target_id and entity properties.
      parent::getPropertyDefinitions();

      static::$propertyDefinitions[$target_type]['revision_id'] = array(
        // @todo: Lookup the entity type's ID data type and use it here.
        'type' => 'integer',
        'label' => t('Revision ID'),
        'constraints' => array(
          'Range' => array('min' => 0),
        ),
      );
      static::$propertyDefinitions[$target_type]['label'] = array(
        'type' => 'string',
        'label' => t('Label (auto-create)'),
        'computed' => TRUE,
      );
      static::$propertyDefinitions[$target_type]['access'] = array(
        'type' => 'boolean',
        'label' => t('Access'),
        'computed' => TRUE,
      );
    }
    return static::$propertyDefinitions[$target_type];
  }

  /**
   * {@inheritdoc}
   *
   * Duplicated from \Drupal\field\Plugin\field\field_type\LegacyCFieldItem,
   * since we cannot extend it.
   */
  public static function schema(Field $field) {
    $definition = \Drupal::typedData()->getDefinition('field_type:' . $field->type);
    $module = $definition['module'];
    module_load_install($module);
    $callback = "{$module}_field_schema";
    if (function_exists($callback)) {
      return $callback($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // Avoid loading the entity by first checking the 'target_id'.
    $target_id = $this->get('target_id')->getValue();
    if (!empty($target_id) && is_numeric($target_id)) {
      return FALSE;
    }
    if (empty($target_id) && ($entity = $this->get('entity')->getValue()) && $entity->isNew()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   *
   * Duplicated from \Drupal\field\Plugin\field\field_type\LegacyCFieldItem,
   * since we cannot extend it.
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    if ($callback = $this->getLegacyCallback('settings_form')) {
      // hook_field_settings_form() used to receive the $instance (not actually
      // needed), and the value of field_has_data().
      return $callback($this->instance->getField(), $this->instance, $has_data);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Duplicated from \Drupal\field\Plugin\field\field_type\LegacyCFieldItem,
   * since we cannot extend it.
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    if ($callback = $this->getLegacyCallback('instance_settings_form')) {
      return $callback($this->instance->getField(), $this->instance, $form_state);
    }
  }

  /**
   * Returns the legacy callback for a given field type "hook".
   *
   * Duplicated from \Drupal\field\Plugin\field\field_type\LegacyCFieldItem,
   * since we cannot extend it.
   *
   * @param string $hook
   *   The name of the hook, e.g. 'settings_form', 'is_empty'.
   *
   * @return string|null
   *   The name of the legacy callback, or NULL if it does not exist.
   */
  protected function getLegacyCallback($hook) {
    $module = $this->pluginDefinition['module'];
    $callback = "{$module}_field_{$hook}";
    if (function_exists($callback)) {
      return $callback;
    }
  }

}
