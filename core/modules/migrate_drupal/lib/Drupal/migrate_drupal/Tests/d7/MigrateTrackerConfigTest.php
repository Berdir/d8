<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d7\MigrateTrackerConfigTest.
 */

namespace Drupal\migrate_drupal\Tests\d7;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of variables from the Tracker module.
 */
class MigrateTrackerConfigTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tracker');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to tracker.*.yml',
      'description'  => 'Upgrade variables to tracker.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of tracker settings variables to tracker.settings.yml.
   */
  public function testTrackerSettings() {
    $migration = entity_load('migration', 'd7_tracker_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal7TrackerSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
    $config = \Drupal::config('tracker.settings');
    $this->assertIdentical($config->get('cron_index_limit'), 1000);
  }
  
}
