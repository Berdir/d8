<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUploadInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutable;

class MigrateUploadInstanceTest extends MigrateDrupal6TestBase {

  static $modules = array('file');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate upload field instance.',
      'description'  => 'Upload field instance migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the field instance migration.
   */
  public function testUploadFieldInstance() {
    // Add some node mappings to get past checkRequirements().
    $id_mappings = array(
      'd6_field_instance' => array(
        array(array('page', 'fieldname'), array('page', 'fieldname')),
      ),
      'd6_upload_field' => array(
        array(array(1), array('upload')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    foreach (array('page', 'story') as $type) {
      entity_create('node_type', array('type' => $type))->save();
    }
    entity_create('field_entity', array(
      'entity_type' => 'node',
      'name' => 'upload',
      'type' => 'file',
      'translatable' => '0',
    ))->save();

    $migration = entity_load('migration', 'd6_upload_field_instance');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UploadInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $field = entity_load('field_instance', 'node.page.upload');
    $this->assertEqual($field->id(), 'node.page.upload');
    $field = entity_load('field_instance', 'node.story.upload');
    $this->assertEqual($field->id(), 'node.story.upload');

    // Shouldn't exist.
    $field = entity_load('field_instance', 'node.article.upload');
    $this->assertTrue(is_null($field));

    $this->assertEqual(array('node', 'page', 'upload'), $migration->getIdMap()->lookupDestinationID(array('page')));
  }

}
