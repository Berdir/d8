<?php

/**
 * @file
 * Definition of Drupal\field\Tests\FieldImportCreateTest.
 */

namespace Drupal\field\Tests;

use Symfony\Component\Yaml\Parser;

/**
 * Tests the functionality of creating fields and instances
 * on hook_config_import_create().
 */
class FieldImportCreateTest extends FieldUnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Field config create tests',
      'description' => 'Create field and instances during config create method invocation.',
      'group' => 'Field API',
    );
  }

  function setUp() {
    parent::setUp();

    $this->field_test_import_staging = 'field.field.field_test_import_staging';
    $this->instance_test_import_staging = 'field.instance.test_entity.test_bundle.field_test_import_staging';
    $this->field_manifest = 'manifest.field.field';
    $this->instance_manifest = 'manifest.field.instance';
  }

  /**
   * Test importing new fields.
   */
  function testImportCreate() {

    $field = entity_load('field_entity', 'field_test_import');
    $this->assertFalse($field);

    // Enable field_test_config module and assert the test import
    // field and instance is available on the Test content type.
    // This tests creating fields and instances that are provided
    // by a module.
    module_enable(array('field_test_config'));
    $field = entity_load('field_entity', 'field_test_import');
    $this->assertTrue($field, 'Test import field exists');
    $instance = entity_load('field_instance', 'test_entity.test_bundle.field_test_import');
    $this->assertTrue($instance, 'Test import field instance exists');

    $module_path = drupal_get_path('module', 'field_test_config');

    // Copy another field and instance to the staging directory
    // on the Test content type and run config_import() to test
    // importing from the staging directory.
    $active = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');
    $field_manifest = $active->read($this->field_manifest);
    $instance_manifest = $active->read($this->instance_manifest);

    // Copy the files.
    $copied = file_unmanaged_copy($module_path .'/staging/' . $this->field_test_import_staging . '.yml', 'public://config_staging/' . $this->field_test_import_staging . '.yml');
    $this->assertTrue($copied);
    $copied = file_unmanaged_copy($module_path .'/staging/' . $this->instance_test_import_staging . '.yml', 'public://config_staging/' . $this->instance_test_import_staging . '.yml');
    $this->assertTrue($copied);

    // Add to manifest.
    $field_manifest['field_test_import_staging'] = array('name' => $this->field_test_import_staging);
    $instance_manifest['node.test_import.field_test_import_staging'] = array('name' => $this->instance_test_import_staging);

    // Write to manifest and new config.
    $staging->write($this->field_manifest, $field_manifest);
    $staging->write($this->instance_manifest, $instance_manifest);

    // Import.
    config_import();

    // Assert the staging field is there.
    $field = entity_load('field_entity', 'field_test_import_staging');
    $this->assertTrue($field, 'Test import field from staging exists');
    $instance = entity_load('field_instance', 'test_entity.test_bundle.field_test_import_staging');
    $this->assertTrue($instance, 'Test import field instance from staging exists');
  }
}
