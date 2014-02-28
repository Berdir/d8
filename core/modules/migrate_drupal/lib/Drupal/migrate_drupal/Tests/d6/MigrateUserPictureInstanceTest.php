<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserPictureInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserPictureInstanceTest extends MigrateDrupalTestBase {

  static $modules = array('image');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user picture field instance.',
      'description'  => 'User picture field instance migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the field instance migration.
   */
  public function testUserPictureFieldInstance() {
    // Add some node mappings to get past checkRequirements().
    $id_mappings = array(
      'd6_user_picture_field' => array(
        array(array('user_upload'), array('name', 'bundle')),
      ),
    );
    $this->prepareIdMappings($id_mappings);
    entity_create('field_config', array(
      'entity_type' => 'user',
      'name' => 'user_picture',
      'type' => 'image',
      'translatable' => '0',
    ))->save();

    $migration = entity_load('migration', 'd6_user_picture_field_instance');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $field = entity_load('field_instance_config', 'user.user.user_picture');
    $settings = $field->getSettings();
    $this->assertEqual($settings['file_extensions'], 'png gif jpg jpeg');
    $this->assertEqual($settings['file_directory'], 'pictures');
    $this->assertEqual($settings['max_filesize'], '30KB');
    $this->assertEqual($settings['max_resolution'], '85x85');

    $this->assertEqual(array('user', 'user', 'user_picture'), $migration->getIdMap()->lookupDestinationID(array('')));
  }

}
