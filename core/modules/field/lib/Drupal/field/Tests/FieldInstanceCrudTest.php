<?php

/**
 * @file
 * Definition of Drupal\field\Tests\FieldInstanceCrudTest.
 */

namespace Drupal\field\Tests;

use Drupal\field\FieldException;

class FieldInstanceCrudTest extends FieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test');

  protected $field;

  public static function getInfo() {
    return array(
      'name' => 'Field instance CRUD tests',
      'description' => 'Create field entities by attaching fields to entities.',
      'group' => 'Field API',
    );
  }

  function setUp() {
    parent::setUp();

    $this->field = array(
      'field_name' => drupal_strtolower($this->randomName()),
      'type' => 'test_field',
    );
    field_create_field($this->field);
    $this->instance_definition = array(
      'field_name' => $this->field['field_name'],
      'entity_type' => 'test_entity',
      'bundle' => 'test_bundle',
    );
  }

  // TODO : test creation with
  // - a full fledged $instance structure, check that all the values are there
  // - a minimal $instance structure, check all default values are set
  // defer actual $instance comparison to a helper function, used for the two cases above,
  // and for testUpdateFieldInstance

  /**
   * Test the creation of a field instance.
   */
  function testCreateFieldInstance() {
    field_create_instance($this->instance_definition);

    // Read the raw record from the {field_config_instance} table.
    $result = db_query('SELECT * FROM {field_config_instance} WHERE field_name = :field_name AND bundle = :bundle', array(':field_name' => $this->instance_definition['field_name'], ':bundle' => $this->instance_definition['bundle']));
    $record = $result->fetchAssoc();
    $record['data'] = unserialize($record['data']);

    $field_type = field_info_field_types($this->field['type']);
    $widget_type = field_info_widget_types($field_type['default_widget']);

    // Check that the ID key is filled in.
    $this->assertIdentical($record['id'], $this->instance_definition['id'], 'The instance id is filled in');

    // Check that default values are set.
    $this->assertIdentical($record['data']['required'], FALSE, 'Required defaults to false.');
    $this->assertIdentical($record['data']['label'], $this->instance_definition['field_name'], 'Label defaults to field name.');
    $this->assertIdentical($record['data']['description'], '', 'Description defaults to empty string.');
    $this->assertIdentical($record['data']['widget']['type'], $field_type['default_widget'], 'Default widget has been written.');

    // Check that default settings are set.
    $this->assertIdentical($record['data']['settings'], $field_type['instance_settings'] , 'Default instance settings have been written.');
    $this->assertIdentical($record['data']['widget']['settings'], $widget_type['settings'] , 'Default widget settings have been written.');

    // Guarantee that the field/bundle combination is unique.
    try {
      field_create_instance($this->instance_definition);
      $this->fail(t('Cannot create two instances with the same field / bundle combination.'));
    }
    catch (FieldException $e) {
      $this->pass(t('Cannot create two instances with the same field / bundle combination.'));
    }

    // Check that the specified field exists.
    try {
      $this->instance_definition['field_name'] = $this->randomName();
      field_create_instance($this->instance_definition);
      $this->fail(t('Cannot create an instance of a non-existing field.'));
    }
    catch (FieldException $e) {
      $this->pass(t('Cannot create an instance of a non-existing field.'));
    }

    // Create a field restricted to a specific entity type.
    $field_restricted = array(
      'field_name' => drupal_strtolower($this->randomName()),
      'type' => 'test_field',
      'entity_types' => array('test_cacheable_entity'),
    );
    field_create_field($field_restricted);

    // Check that an instance can be added to an entity type allowed
    // by the field.
    try {
      $instance = $this->instance_definition;
      $instance['field_name'] = $field_restricted['field_name'];
      $instance['entity_type'] = 'test_cacheable_entity';
      field_create_instance($instance);
      $this->pass(t('Can create an instance on an entity type allowed by the field.'));
    }
    catch (FieldException $e) {
      $this->fail(t('Can create an instance on an entity type allowed by the field.'));
    }

    // Check that an instance cannot be added to an entity type
    // forbidden by the field.
    try {
      $instance = $this->instance_definition;
      $instance['field_name'] = $field_restricted['field_name'];
      field_create_instance($instance);
      $this->fail(t('Cannot create an instance on an entity type forbidden by the field.'));
    }
    catch (FieldException $e) {
      $this->pass(t('Cannot create an instance on an entity type forbidden by the field.'));
    }

    // TODO: test other failures.
  }

  /**
   * Test reading back an instance definition.
   */
  function testReadFieldInstance() {
    field_create_instance($this->instance_definition);

    // Read the instance back.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $this->assertTrue($this->instance_definition == $instance, 'The field was properly read.');
  }

  /**
   * Test the update of a field instance.
   */
  function testUpdateFieldInstance() {
    field_create_instance($this->instance_definition);

    // Check that basic changes are saved.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $instance['required'] = !$instance['required'];
    $instance['label'] = $this->randomName();
    $instance['description'] = $this->randomName();
    $instance['settings']['test_instance_setting'] = $this->randomName();
    $instance['widget']['settings']['test_widget_setting'] =$this->randomName();
    $instance['widget']['weight']++;
    field_update_instance($instance);

    $instance_new = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $this->assertEqual($instance['required'], $instance_new['required'], '"required" change is saved');
    $this->assertEqual($instance['label'], $instance_new['label'], '"label" change is saved');
    $this->assertEqual($instance['description'], $instance_new['description'], '"description" change is saved');
    $this->assertEqual($instance['widget']['settings']['test_widget_setting'], $instance_new['widget']['settings']['test_widget_setting'], 'Widget setting change is saved');
    $this->assertEqual($instance['widget']['weight'], $instance_new['widget']['weight'], 'Widget weight change is saved');

    // Check that changing the widget type updates the default settings.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $instance['widget']['type'] = 'test_field_widget_multiple';
    field_update_instance($instance);

    $instance_new = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $this->assertEqual($instance['widget']['type'], $instance_new['widget']['type'] , 'Widget type change is saved.');
    $settings = field_info_widget_settings($instance_new['widget']['type']);
    $this->assertIdentical($settings, array_intersect_key($instance_new['widget']['settings'], $settings) , 'Widget type change updates default settings.');

    // TODO: test failures.
  }

  /**
   * Test the deletion of a field instance.
   */
  function testDeleteFieldInstance() {
    // TODO: Test deletion of the data stored in the field also.
    // Need to check that data for a 'deleted' field / instance doesn't get loaded
    // Need to check data marked deleted is cleaned on cron (not implemented yet...)

    // Create two instances for the same field so we can test that only one
    // is deleted.
    field_create_instance($this->instance_definition);
    $this->another_instance_definition = $this->instance_definition;
    $this->another_instance_definition['bundle'] .= '_another_bundle';
    $instance = field_create_instance($this->another_instance_definition);

    // Test that the first instance is not deleted, and then delete it.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle'], array('include_deleted' => TRUE));
    $this->assertTrue(!empty($instance) && empty($instance['deleted']), 'A new field instance is not marked for deletion.');
    field_delete_instance($instance);

    // Make sure the instance is marked as deleted when the instance is
    // specifically loaded.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle'], array('include_deleted' => TRUE));
    $this->assertTrue(!empty($instance['deleted']), 'A deleted field instance is marked for deletion.');

    // Try to load the instance normally and make sure it does not show up.
    $instance = field_read_instance('test_entity', $this->instance_definition['field_name'], $this->instance_definition['bundle']);
    $this->assertTrue(empty($instance), 'A deleted field instance is not loaded by default.');

    // Make sure the other field instance is not deleted.
    $another_instance = field_read_instance('test_entity', $this->another_instance_definition['field_name'], $this->another_instance_definition['bundle']);
    $this->assertTrue(!empty($another_instance) && empty($another_instance['deleted']), 'A non-deleted field instance is not marked for deletion.');

    // Make sure the field is deleted when its last instance is deleted.
    field_delete_instance($another_instance);
    $field = field_read_field($another_instance['field_name'], array('include_deleted' => TRUE));
    $this->assertTrue(!empty($field['deleted']), 'A deleted field is marked for deletion after all its instances have been marked for deletion.');
  }
}
