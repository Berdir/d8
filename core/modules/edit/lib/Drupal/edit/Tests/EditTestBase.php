<?php

/**
 * @file
 * Contains \Drupal\edit\Tests\EditTestBase.
 */

namespace Drupal\edit\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Parent class for Edit tests.
 */
class EditTestBase extends DrupalUnitTestBase {
  var $default_storage = 'field_sql_storage';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'entity', 'field_test', 'field', 'number', 'text', 'edit');

  /**
   * Sets the default field storage backend for fields created during tests.
   */
  function setUp() {
    parent::setUp();

    $this->installSchema('system', 'variable');
    $this->enableModules(array('field', 'field_sql_storage', 'field_test'));

    // Set default storage backend.
    variable_set('field_storage_default', $this->default_storage);
  }

  /**
   * Creates a field and an instance of it.
   *
   * @param string $field_name
   *   The field name.
   * @param string $type
   *   The field type.
   * @param int $cardinality
   *   The field's cardinality.
   * @param string $label
   *   The field's label (used everywhere: widget label, formatter label).
   * @param array $instance_settings
   * @param string $widget_type
   *   The widget type.
   * @param array $widget_settings
   *   The widget settings.
   * @param string $formatter_type
   *   The formatter type.
   * @param array $formatter_settings
   *   The formatter settings.
   */
  function createFieldWithInstance($field_name, $type, $cardinality, $label, $instance_settings, $widget_type, $widget_settings, $formatter_type, $formatter_settings) {
    $field = $field_name . '_field';
    $this->$field = array(
      'field_name' => $field_name,
      'type' => $type,
      'cardinality' => $cardinality,
    );
    $this->$field_name = field_create_field($this->$field);

    $instance = $field_name . '_instance';
    $this->$instance = array(
      'field_name' => $field_name,
      'entity_type' => 'test_entity',
      'bundle' => 'test_bundle',
      'label' => $label,
      'description' => $label,
      'weight' => mt_rand(0, 127),
      'settings' => $instance_settings,
      'widget' => array(
        'type' => $widget_type,
        'label' => $label,
        'settings' => $widget_settings,
      ),
    );
    field_create_instance($this->$instance);

    entity_get_display('test_entity', 'test_bundle', 'default')
      ->setComponent($field_name, array(
        'label' => 'above',
        'type' => $formatter_type,
        'settings' => $formatter_settings
      ))
      ->save();
  }
}
