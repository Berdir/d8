<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFileTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests the Drupal 6 files to Drupal 8 migration.
 */
class MigrateFileTest extends MigrateDrupalTestBase {

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
      'name' => 'Migrate files',
      'description' => 'file migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests the Drupal 6 files to Drupal 8 migration.
   */
  public function testFiles() {
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6File.php',
    );
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_file');
    $migration->source['conf_path'] = 'core/modules/simpletest';
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    /** @var \Drupal\file\FileInterface $file */
    $file = entity_load('file', 1);
    $this->assertEqual($file->getFilename(), 'Image1.jpg');
    $this->assertEqual($file->getSize(), 1831);
    $this->assertEqual($file->getFileUri(), 'public://image-1.jpg');
    $this->assertEqual($file->getMimeType(), 'image/jpeg');

    // Test that we can re-import and also test with file_directory_path set.
    db_truncate($migration->getIdMap()->mapTableName())->execute();
    $migration = entity_load_unchanged('migration', 'd6_file');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemFile.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $file = entity_load('file', 2);
    $this->assertEqual($file->getFileUri(), 'public://core/modules/simpletest/files/image-2.jpg');
  }

}
