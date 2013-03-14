<?php

/**
 * @file
 * Definition of Drupal\field\Tests\FieldImportDeleteTest.
 */

namespace Drupal\field\Tests;

/**
 * Tests the functionality of deleting fields and instances
 * on hook_config_import_delete().
 */
class FieldImportDeleteTest extends FieldUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test_config');

  public static function getInfo() {
    return array(
      'name' => 'Field config delete tests',
      'description' => 'Delete field and instances during config delete method invocation.',
      'group' => 'Field API',
    );
  }

  function setUp() {
    parent::setUp();

    $this->test_import_field_name = 'field.field.test_import';
    $this->test_import_instance_name = 'field.instance.node.test_import.field_test_import';
    $this->field_manifest = 'manifest.field.field';
    $this->instance_manifest = 'manifest.field.instance';
  }

  /**
   * Test importing deletions.
   */
  function testImportDelete() {

    // Import default config.
    config_install_default_config('module', 'field_test_config');

    // Check the field exists.
    $field_test_import = field_info_field('field_test_import');

    // Delete body field and instance, the test import instance
    // from the manifest.
    $active = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');
    $field_manifest = $active->read($this->field_manifest);
    unset($field_manifest['field_test_import']);
    $instance_manifest = $active->read($this->instance_manifest);
    unset($instance_manifest['test_entity.test_bundle.field_test_import']);
    $staging->write($this->field_manifest, $field_manifest);
    $staging->write($this->instance_manifest, $instance_manifest);

    // Import.
    config_import();

    // Assert the field and instance are gone.
    $field = entity_load('field_entity', 'field_test_import', TRUE);
    $this->assertFalse($field, 'Test import field is removed.');
    $instance = entity_load('field_instance', 'test_entity.test_bundle.field_test_import', TRUE);
    $this->assertFalse($instance, 'Test import field instance is removed.');

    // Check that import field is in state of deleted fields.
    $deleted_fields = state()->get('field.field.deleted') ?: array();
    $this->assertTrue(isset($deleted_fields[$field_test_import->uuid]));

    // Run purge_batch().
    field_purge_batch(10);

    // Check that the deleted fields are removed from state.
    $deleted_fields = state()->get('field.field.deleted') ?: array();
    $this->assertTrue(empty($deleted_fields), 'Fields are deleted');

    // Check all config files are gone.
    $active = $this->container->get('config.storage');
    $this->assertIdentical($active->listAll($this->test_import_field_name), array());
    $this->assertIdentical($active->listAll($this->test_import_instance_name), array());
  }
}
