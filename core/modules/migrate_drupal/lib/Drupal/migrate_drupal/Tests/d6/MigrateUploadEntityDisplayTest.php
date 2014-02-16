<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUploadEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutable;

class MigrateUploadEntityDisplayTest extends MigrateDrupal6TestBase {

  static $modules = array('file');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate upload entity display.',
      'description'  => 'Upload entity display',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the upload field entity display migration.
   */
  public function testUploadEntityDisplay() {

    $id_mappings = array(
      'd6_field' => array(
        array(array('upload'), array('upload')),
      ),
      'd6_field_instance' => array(
        array(array('page', 'fieldname'), array('page', 'fieldname')),
      ),
      'd6_upload_field_instance' => array(
        array(array(1), array('page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_upload_entity_display');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UploadInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $display = entity_get_display('node', 'page', 'default');
    $component = $display->getComponent('upload');
    $this->assertEqual($component['type'], 'file_default');

    $display = entity_get_display('node', 'story', 'default');
    $component = $display->getComponent('upload');
    $this->assertEqual($component['type'], 'file_default');

    // Assure this doesn't exist.
    $display = entity_get_display('node', 'article', 'default');
    $component = $display->getComponent('upload');
    $this->assertTrue(is_null($component));

    $this->assertEqual(array('node', 'page', 'default', 'upload'), $migration->getIdMap()->lookupDestinationID(array('page')));
  }

}
