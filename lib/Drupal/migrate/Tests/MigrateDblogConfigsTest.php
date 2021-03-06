<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateDblogConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the dblog module.
 */
class MigrateDblogConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to dblog.settings.yml',
      'description'  => 'Upgrade variables to dblog.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of dblog variables to dblog.settings.yml.
   */
  public function testBookSettings() {
    $migration = entity_load('migration', 'd6_dblog_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6DblogSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('dblog.settings');
    $this->assertIdentical($config->get('row_limit'), 1000);
  }
}
