<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateStatisticsConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Statistics module.
 */
class MigrateStatisticsConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to statistics.settings.yml',
      'description'  => 'Upgrade variables to statistics.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of statistics variables to statistics.settings.yml.
   */
  public function testStatisticsSettings() {
    $migration = entity_load('migration', 'd6_statistics_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6StatisticsSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('statistics.settings');
    $this->assertIdentical($config->get('access_log.enable'), 0);
    $this->assertIdentical($config->get('access_log.max_lifetime'), 259200);
    $this->assertIdentical($config->get('count_content_views'), NULL);
    $this->assertIdentical($config->get('block.popular.top_day_limit'), 0);
    $this->assertIdentical($config->get('block.popular.top_all_limit'), 0);
    $this->assertIdentical($config->get('block.popular.top_recent_limit'), 0);
  }
}
