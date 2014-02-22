<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserPictureEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserPictureEntityDisplayTest extends MigrateDrupalTestBase {

  static $modules = array('image');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user picture entity display.',
      'description'  => 'User picture entity display',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the user picture field entity display migration.
   */
  public function testUserPictureEntityDisplay() {

    $id_mappings = array(
      'd6_user_picture_field_instance' => array(
        array(array(1), array('user', 'user', 'user_picture')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_user_picture_entity_display');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $display = entity_get_display('user', 'user', 'default');
    $component = $display->getComponent('user_picture');
    $this->assertEqual($component['type'], 'image');
    $this->assertEqual($component['settings']['image_link'], 'content');

    $this->assertEqual(array('user', 'user', 'default', 'user_picture'), $migration->getIdMap()->lookupDestinationID(array('')));
  }

}
