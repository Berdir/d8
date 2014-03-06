<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserPictureFileTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests the Drupal 6 user pictures to Drupal 8 migration.
 */
class MigrateUserPictureFileTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('file');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate user pictures',
      'description' => 'User pictures migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6User.php',
    );
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_user_picture_file');
    $migration->source['conf_path'] = 'core/modules/simpletest';
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests the Drupal 6 user pictures to Drupal 8 migration.
   */
  public function testUserPictures() {
    /** @var \Drupal\file\FileInterface $file */
    $file = entity_load('file', 1);
    $this->assertEqual($file->getFilename(), 'image-test.jpg');
    $this->assertEqual($file->getFileUri(), 'public://image-test.jpg');
    $this->assertEqual($file->getSize(), 1901);
    $this->assertEqual($file->getMimeType(), 'image/jpeg');

    $file = entity_load('file', 2);
    $this->assertEqual($file->getFilename(), 'image-test.png');
    $this->assertEqual($file->getFileUri(), 'public://image-test.png');

    $this->assertEqual(array(1), entity_load('migration', 'd6_user_picture_file')->getIdMap()->lookupDestinationID(array(2)));
  }

}
