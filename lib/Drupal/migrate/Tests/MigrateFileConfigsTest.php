<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateFileConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the File module.
 */
class MigrateFileConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to file.settings.yml',
      'description'  => 'Upgrade variables to file.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of file variables to file.settings.yml.
   */
  public function testFileSettings() {
    $migration = entity_load('migration', 'd6_file_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6FileSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('file.settings');
    $this->assertIdentical($config->get('description.type'), 'textfield');
    $this->assertIdentical($config->get('description.length'), 128);
    $this->assertIdentical($config->get('icon.directory'), 'sites/default/files/icons');
  }
}
