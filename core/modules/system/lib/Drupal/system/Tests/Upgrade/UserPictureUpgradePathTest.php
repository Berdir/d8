<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Upgrade\UserPictureUpgradePathTest.
 */

namespace Drupal\system\Tests\Upgrade;

/**
 * Tests upgrading a filled database with user picture data.
 *
 * Loads a filled installation of Drupal 7 with user picture data and runs the
 * upgrade process on it.
 */
class UserPictureUpgradePathTest extends UpgradePathTestBase {

  public static function getInfo() {
    return array(
      'name'  => 'User picture upgrade test',
      'description'  => 'Upgrade tests with user picture data.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    $path = drupal_get_path('module', 'system') . '/tests/upgrade';
    $this->databaseDumpFiles = array(
      $path . '/drupal-7.bare.standard_all.database.php.gz',
      $path . '/drupal-7.user_picture.database.php',
    );
    parent::setUp();
  }

  /**
   * Tests expected user picture conversions after a successful upgrade.
   */
  public function testUserPictureUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Retrieve the field instance and check for migrated settings.
    $instance = field_info_instance('user', 'user_picture', 'user');
    $file = entity_load('file', $instance['settings']['default_image']);
    $this->assertIdentical($instance['settings']['default_image'], $file->id(), 'Default user picture has been migrated.');
    $this->assertEqual($file->uri, 'public://user_pictures_dir/druplicon.png', 'File id matches the uri expected.');
    $this->assertEqual($instance['settings']['max_resolution'], '800x800', 'User picture maximum resolution has been migrated.');
    $this->assertEqual($instance['settings']['max_filesize'], '700 KB', 'User picture maximum filesize has been migrated.');
    $this->assertEqual($instance['description'], 'These are user picture guidelines.', 'User picture guidelines are now the user picture field description.');
    $this->assertEqual($instance['settings']['file_directory'], 'user_pictures_dir', 'User picture directory path has been migrated.');
    $this->assertEqual($instance['display']['default']['settings']['image_style'], 'thumbnail', 'User picture image style setting has been migrated.');
  }

}
