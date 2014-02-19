<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeBodyInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateNodeBodyInstanceTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate node body instances to field.instance.node.*.body.yml',
      'description'  => 'Upgrade node body instances to field.instance.node.*.body.yml',
      'group' => 'Migrate Drupal',
    );
  }

  public function testNodeBodyInstance() {
    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_field' => array(
        array(array('field_name'), array('field_name')),
      ),
      'd6_field_instance' => array(
        array(array('fieldname', 'page'), array('fieldname', 'page')),
      ),
      'd6_node_type' => array(
        array(array('page'), array('page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $field = entity_create('field_entity', array(
      'name' => 'body',
      'entity_type' => 'node',
      'type' => 'text_with_summary',
    ));
    $field->save();

    $migration = entity_load('migration', 'd6_node_body_instance');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeBodyInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the body field instance is created for a content type.
    $field = entity_load('field_instance', 'node.company.body');
    $this->assertEqual($field->label(), 'Description', 'Field body label correct');
    $expected = array('display_summary' => true, 'text_processing' => true);
    $this->assertEqual($field->getSettings(), $expected, 'Field body settings are correct.');
    $this->assertEqual(array(), $field->default_value, 'Field body default_value is correct.');

    // Test that the body field instance is created for a second content type.
    $field = entity_load('field_instance', 'node.employee.body');
    $this->assertEqual($field->label(), 'Bio', 'Field body label correct');
    $expected = array('display_summary' => true, 'text_processing' => true);
    $this->assertEqual($field->getSettings(), $expected, 'Field body settings are correct.');
    $this->assertEqual(array(), $field->default_value, 'Field body default_value is correct.');

    // Test that the body field instance is skipped if the has_body is set to
    // false in the source.
    $field = entity_load('field_instance', 'node.sponsor.body');
    $this->assertNull($field, 'The body must not be created.');

    // Ensure Id map works.
    $this->assertEqual(array('node', 'company', 'body'), $migration->getIdMap()->lookupDestinationID(array('company')));
    $this->assertEqual(array('node', 'employee', 'body'), $migration->getIdMap()->lookupDestinationID(array('employee')));
    // The skipped body node key should not be added to the Id map.
    $this->assertEqual(array(NULL, NULL, NULL), $migration->getIdMap()->lookupDestinationID(array('sponsor')));
  }
}
