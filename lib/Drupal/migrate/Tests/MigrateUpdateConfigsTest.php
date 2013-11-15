<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateUpdateConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Update module.
 */
class MigrateUpdateConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to update.settings.yml',
      'description'  => 'Upgrade variables to update.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of update variables to update.settings.yml.
   */
  public function testUpdateSettings() {
    $migration = entity_load('migration', 'd6_update_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6UpdateSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('update.settings');
    $this->assertIdentical($config->get('fetch.max_attempts'), 2);
    $this->assertIdentical($config->get('fetch.url'), 'http://updates.drupal.org/release-history');
    $this->assertIdentical($config->get('notification.threshold'), 'all');
    $this->assertIdentical($config->get('notification.mails'), array());
  }
}
