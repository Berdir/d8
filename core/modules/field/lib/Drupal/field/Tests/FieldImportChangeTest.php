<?php

/**
 * @file
 * Definition of Drupal\field\Tests\FieldImportChangeTest.
 */

namespace Drupal\field\Tests;

/**
 * Tests the functionality of deleting fields and instances
 * on hook_config_import_change().
 */
class FieldImportChangeTest extends FieldUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test_config');

  public static function getInfo() {
    return array(
      'name' => 'Field config change tests',
      'description' => 'Update field and instances during config change method invocation.',
      'group' => 'Field API',
    );
  }

  function setUp() {
    parent::setUp();
    $this->instance_manifest = 'manifest.field.instance';
    $this->instance_name = 'field.instance.test_entity.test_bundle.field_test_import';
  }

  /**
   * Test importing changes.
   */
  function testImportChange() {

    // Import default config.
    config_install_default_config('module', 'field_test_config');

    // Assert default test import.
    $field = entity_load('field_entity', 'field_test_import');
    $this->assertTrue($field, 'Test import field exists');
    $instance = entity_load('field_instance', 'test_entity.test_bundle.field_test_import');
    $this->assertTrue($instance, 'Test import field instance exists');

    // Change label.
    $active = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');
    $manifest = $active->read($this->instance_manifest);
    $instance = $active->read($this->instance_name);
    $new_label = 'Test update import field';
    $instance['label'] = $new_label;
    $staging->write($this->instance_name, $instance);
    $staging->write($this->instance_manifest, $manifest);

    // Import.
    config_import();

    // Assert updated label.
    $instance = entity_load('field_instance', 'test_entity.test_bundle.field_test_import');
    $this->assertEqual($instance['label'], $new_label, 'Instance label updated');
  }
}
