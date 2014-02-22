<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserPictureFileTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserPictureFileTest extends MigrateDrupalTestBase {

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

  public function testUserPictures() {

    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6User.php',
    );
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_user_picture_file');
    $migration->source['conf_path'] = 'core/modules/simpletest';
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    /** @var \Drupal\file\FileInterface $file */
    $file = entity_load('file', 1);
    $this->assertEqual($file->getFilename(), 'image-1.png');
    $this->assertEqual($file->getFileUri(), 'public://image-1.png');
    $this->assertEqual($file->getSize(), 39325);
    $this->assertEqual($file->getMimeType(), 'image/png');

    $file = entity_load('file', 2);
    $this->assertEqual($file->getFilename(), 'image-2.jpg');
    $this->assertEqual($file->getFileUri(), 'public://image-2.jpg');

    $this->assertEqual(array(1), $migration->getIdMap()->lookupDestinationID(array(2)));
  }

}
