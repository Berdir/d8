<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateAggregatorConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Aggregator module.
 */
class MigrateAggregatorConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to aggregator.settings.yml',
      'description'  => 'Upgrade variables to aggregator.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of aggregator variables to aggregator.settings.yml.
   */
  public function testAggregatorSettings() {
    $migration = entity_load('migration', 'd6_aggregator_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6AggregatorSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('aggregator.settings');
    $this->assertIdentical($config->get('fetcher'), 'aggregator');
    $this->assertIdentical($config->get('parser'), 'aggregator');
    $this->assertIdentical($config->get('processors'), array('aggregator'));
    $this->assertIdentical($config->get('items.teaser_length'), '600');
    $this->assertIdentical($config->get('items.allowed_html'), '<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>');
    $this->assertIdentical($config->get('items.expire'), '9676800');
    $this->assertIdentical($config->get('source.list_max'), '3');
    $this->assertIdentical($config->get('source.category_selector'), 'checkboxes');
  }
}
