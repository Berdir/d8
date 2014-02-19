<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUploadInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUploadFieldTest extends MigrateDrupalTestBase {

  static $modules = array('file', 'node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate uploads',
      'description'  => 'Uploads migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the field migration.
   */
  public function testUpload() {
    // Add some node mappings to get past checkRequirements().
    $id_mappings = array(
      'd6_node' => array(
        array(array(1), array(1)),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_upload_field');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UploadField.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $field = entity_load('field_entity', 'node.upload');
    $this->assertEqual($field->id(), 'node.upload');
    $this->assertEqual(array('node', 'upload'), $migration->getIdMap()->lookupDestinationID(array(1)));
  }

}
