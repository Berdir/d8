<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFieldInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of field instances.
 */
class MigrateFieldInstanceTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'number',
    'email',
    'telephone',
    'link',
    'file',
    'image',
    'datetime',
    'node',
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate field instances to field.instance.*.*.*.yml',
      'description' => 'Migrate field instances.',
      'group' => 'Migrate Drupal',
    );
  }

  /*
   * Tests migration of file variables to file.settings.yml.
   */
  public function testFieldInstanceSettings() {
    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_field' => array(
        array(array('field_name'), array('node', 'field_name')),
      ),
      'd6_node_type' => array(
        array(array('page'), array('page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_field_instance');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->createFields();

    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $entity = entity_create('node', array('type' => 'story'));
    // Test a text field.
    $field = entity_load('field_instance_config', 'node.story.field_test');
    $this->assertEqual($field->label(), 'Text Field', 'Field field_test label correct');
    $expected = array('max_length' => 255, 'text_processing' => 1);
    $this->assertEqual($field->getSettings(), $expected, 'Field field_test settings are correct.');
    $this->assertEqual('text for default value', $entity->field_test->value, 'Field field_test default_value is correct.');


    // Test a number field.
    $field = entity_load('field_instance_config', 'node.story.field_test_two');
    $this->assertEqual($field->label(), 'Integer Field');
    $expected = array(
      'min' => '10',
      'max' => '100',
      'prefix' => 'pref',
      'suffix' => 'suf',
    );
    $this->assertEqual($field->getSettings(), $expected, 'Field field_test_two settings are correct.');

    // Test email field.
    $field = entity_load('field_instance_config', 'node.story.field_test_email');
    $this->assertEqual($field->label(), 'Email Field');
    $this->assertEqual('benjy@example.com', $entity->field_test_email->value, 'Field field_test_email default_value is correct.');

    // Test a filefield.
    $field = entity_load('field_instance_config', 'node.story.field_test_filefield');
    $this->assertEqual($field->label(), 'File Field');
    $expected = array(
      'file_extensions' => 'txt pdf doc',
      'file_directory' => 'images',
      'description_field' => TRUE,
      'max_filesize' => '200KB',
      'target_type' => 'file',
      'display_field' => FALSE,
      'display_default' => FALSE,
      'uri_scheme' => 'public',
    );
    // This is the only way to compare arrays.
    $this->assertFalse(array_diff_assoc($field->getSettings(), $expected));
    $this->assertFalse(array_diff_assoc($expected, $field->getSettings()));

    // Test a link field.
    $field = entity_load('field_instance_config', 'node.story.field_test_link');
    $this->assertEqual($field->label(), 'Link Field');
    $expected = array('title' => 2);
    $this->assertEqual($field->getSettings(), $expected, 'Field field_test_link settings are correct.');
    $this->assertEqual('default link title', $entity->field_test_link->title, 'Field field_test_link default title is correct.');
    $this->assertEqual('http://drupal.org', $entity->field_test_link->url, 'Field field_test_link default title is correct.');

  }

  /**
   * Helper to create fields.
   */
  protected function createFields() {
    $fields = array(
      'field_test' => 'text',
      'field_test_two' => 'number_integer',
      'field_test_three' => 'number_float',
      'field_test_email' => 'email',
      'field_test_link' => 'link',
      'field_test_filefield' => 'file',
      'field_test_imagefield' => 'image',
      'field_test_phone' => 'telephone',
      'field_test_date' => 'datetime',
      'field_test_datestamp' => 'datetime',
      'field_test_datetime' => 'datetime',
    );
    foreach ($fields as $name => $type) {
      entity_create('field_config', array(
        'name' => $name,
        'entity_type' => 'node',
        'type' => $type,
      ))->save();
    }

  }

}
