<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserPictureEntityFormDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserPictureEntityFormDisplayTest extends MigrateDrupalTestBase {

  static $modules = array('image');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user picture entity form display.',
      'description'  => 'User picture entity form display',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the user picture field entity display migration.
   */
  public function testUserPictureEntityFormDisplay() {

    $id_mappings = array(
      'd6_user_picture_field_instance' => array(
        array(array(1), array('user', 'user', 'user_picture')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_user_picture_entity_form_display');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $display = entity_get_form_display('user', 'user', 'default');
    $component = $display->getComponent('user_picture');
    $this->assertEqual($component['type'], 'image_image');
    $this->assertEqual($component['settings']['progress_indicator'], 'throbber');

    $this->assertEqual(array('user', 'user', 'default', 'user_picture'), $migration->getIdMap()->lookupDestinationID(array('')));
  }

}
