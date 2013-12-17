<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateSearchConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of variables for the Search module.
 */
class MigrateSearchConfigsTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to search.settings.yml',
      'description'  => 'Upgrade variables to search.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of search variables to search.settings.yml.
   */
  public function testSearchSettings() {
    $migration = entity_load('migration', 'd6_search_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SearchSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('search.settings');
    $this->assertIdentical($config->get('index.minimum_word_size'), '3');
    $this->assertIdentical($config->get('index.overlap_cjk'), 1);
    $this->assertIdentical($config->get('index.cron_limit'), '100');
    // Not sure where these two variables are set in D6.
    $this->assertIdentical($config->get('index.tag_weights'), array(
        'h1' => 25,
        'h2' => 18,
        'h3' => 15,
        'h4' => 12,
        'h5' => 9,
        'h6' => 6,
        'u' => 3,
        'b' => 3,
        'i' => 3,
        'strong' => 3,
        'em' => 3,
        'a' => 10,
      ));
    $this->assertIdentical($config->get('and_or_limit'), 7);
  }
}
