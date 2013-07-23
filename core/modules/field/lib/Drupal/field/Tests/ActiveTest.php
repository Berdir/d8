<?php

/**
 * @file
 * Definition of Drupal\field\Tests\ActiveTest.
 */

namespace Drupal\field\Tests;

class ActiveTest extends FieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test', 'entity_test');

  public static function getInfo() {
    return array(
      'name' => 'Field active test',
      'description' => 'Test that fields are properly marked active or inactive.',
      'group' => 'Field API',
    );
  }

  /**
   * Test that fields are properly marked active or inactive.
   */
  function testActive() {
    $field_definition = array(
      'name' => 'field_1',
      'entity_type' => 'entity_test',
      'type' => 'test_field',
    );
    entity_create('field_entity', $field_definition)->save();

    // Test disabling and enabling the field type module,
    $modules = array('field_test');
    $field_name = $field_definition['name'];

    // Read the field.
    $field = field_read_field('entity_test', $field_name);
    $this->assertTrue($field_definition <= $field, 'The field was properly read.');

    module_disable($modules, FALSE);

    $fields = field_read_fields(array('entity_type' => 'entity_test', 'name' => $field_name), array('include_inactive' => TRUE));
    $this->assertTrue(isset($fields[$field_name]) && $field_definition < $field, 'The field is properly read when explicitly fetching inactive fields.');

    // Re-enable modules one by one, and check that the field is still inactive
    // while some modules remain disabled.
    while ($modules) {
      $field = field_read_field('entity_test', $field_name);
      $this->assertTrue(empty($field), format_string('%modules disabled. The field is marked inactive.', array('%modules' => implode(', ', $modules))));

      $module = array_shift($modules);
      module_enable(array($module), FALSE);
    }

    // Check that the field is active again after all modules have been
    // enabled.
    $field = field_read_field('entity_test', $field_name);
    $this->assertTrue($field_definition <= $field, 'The field was was marked active.');
  }
}
