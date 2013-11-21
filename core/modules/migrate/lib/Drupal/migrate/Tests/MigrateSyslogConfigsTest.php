<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateSyslogConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Syslog module.
 */
class MigrateSyslogConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to syslog.settings.yml',
      'description'  => 'Upgrade variables to syslog.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of syslog variables to syslog.settings.yml.
   */
  public function testSyslogSettings() {
    $migration = entity_load('migration', 'd6_syslog_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6SyslogSettings.php',
    );
    $facility = defined('LOG_LOCAL0') ? LOG_LOCAL0 : LOG_USER;
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('syslog.settings');
    $this->assertIdentical($config->get('identity'), 'drupal');
    $this->assertIdentical($config->get('facility'), $facility);
  }
}
