<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUploadInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the upload field migration.
 */
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
   * Test the upload field migration.
   */
  public function testUpload() {
    $migration = entity_load('migration', 'd6_upload_field');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $field = entity_load('field_entity', 'node.upload');
    $this->assertEqual($field->id(), 'node.upload');
    $this->assertEqual(array('node', 'upload'), $migration->getIdMap()->lookupDestinationID(array('')));
  }

}
