<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Tests\EntityReferenceFieldTest.
 */

namespace Drupal\entity_reference\Tests;

use Drupal\field\FieldValidationException;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests for entity reference field and formatter.
 *
 * @todo Clean up this test class in http://drupal.org/node/1822000.
 */
class EntityReferenceFieldTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'entity', 'field', 'field_sql_storage', 'field_test', 'entity_reference');

  protected $field;
  protected $instance;

  public static function getInfo() {
    return array(
      'name' => 'Entity Reference field',
      'description' => 'Test the creation of entity reference fields.',
      'group' => 'Entity Reference',
    );
  }

  public function setUp() {
    parent::setUp();

    $this->installSchema('system', 'variable');
    $this->installSchema('field', array('field_config', 'field_config_instance'));
    $this->installSchema('field_test', array('test_entity', 'test_entity_revision'));

    // Setup a field and instance.
    $this->field_name = drupal_strtolower($this->randomName());
    $this->field = array(
      'field_name' => $this->field_name,
      'type' => 'entity_reference',
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
      'settings' => array(
        'target_type' => 'test_entity',
      ),
    );
    field_create_field($this->field);

    $this->instance = array(
      'field_name' => $this->field_name,
      'bundle' => 'test_bundle',
      'entity_type' => 'test_entity',
      'widget' => array(
        'type' => 'options_select',
      ),
      'settings' => array(
        'handler' => 'default',
        'handler_settings' => array(
          'target_bundles' => array(
            'test_bundle',
          ),
        ),
      ),
    );
    field_create_instance($this->instance);
  }

  /**
   * Tests reference field validation.
   */
  function testEntityReferenceFieldValidation() {
    // Test valid and invalid values with field_attach_validate().
    $langcode = LANGUAGE_NOT_SPECIFIED;

    $referenced_entity = field_test_create_entity();
    $referenced_entity->save();

    $entity = field_test_create_entity();
    $entity->{$this->field_name}[$langcode][0]['target_id'] = $referenced_entity->id();
    try {
      field_attach_validate($entity);
      $this->pass('Correct reference does not cause validation error.');
    }
    catch (FieldValidationException $e) {
      $this->fail('Correct reference does not cause validation error.');
    }

    $bad_referenced_entity = field_test_create_entity(2, 2, 'test_bundle_2');
    $bad_referenced_entity->save();

    $entity = field_test_create_entity();
    $entity->{$this->field_name}[$langcode][0]['target_id'] = $bad_referenced_entity->id();
    try {
      field_attach_validate($entity);
      $this->fail('Wrong reference causes validation error.');
    }
    catch (FieldValidationException $e) {
      $this->pass('Wrong reference causes validation error.');
    }
  }

  /**
   * Tests that bundle changes are mirrored in field definitions.
   */
  function testEntityReferenceFieldChangeMachineName() {
    // Add several entries in the 'target_bundles' setting, to make sure that
    // they all get updated.
    $test_bundle_2 = drupal_strtolower($this->randomName());
    field_test_create_bundle($test_bundle_2);
    $this->instance['settings']['handler_settings']['target_bundles'] = array(
      $test_bundle_2,
      $test_bundle_2,
      'test_bundle',
    );
    field_update_instance($this->instance);

    // Change the machine name.
    $test_bundle_2_new = drupal_strtolower($this->randomName());
    field_test_rename_bundle($test_bundle_2, $test_bundle_2_new);

    // Check that the field instance settings have changed.
    $instance = field_info_instance('test_entity', $this->field_name, 'test_bundle');
    $target_bundles = $instance['settings']['handler_settings']['target_bundles'];
    $this->assertEqual($target_bundles[0], $test_bundle_2_new, 'Index 0: Machine name was updated correctly.');
    $this->assertEqual($target_bundles[1], $test_bundle_2_new, 'Index 1: Machine name was updated correctly.');
    $this->assertEqual($target_bundles[2], 'test_bundle', 'Index 2: Machine name was left untouched.');

    field_test_delete_bundle($test_bundle_2_new);
    // Check that the field instance settings have changed.
    $instance = field_info_instance('test_entity', $this->field_name, 'test_bundle');
    $target_bundles = $instance['settings']['handler_settings']['target_bundles'];
    $this->assertEqual($target_bundles[0], 'test_bundle', 'Index 0: The bundle that was not removed is still referenced.');
    $this->assertEqual(count($target_bundles[0]), 1, "The 'target_bundles' setting contains only one element.");
  }
}
