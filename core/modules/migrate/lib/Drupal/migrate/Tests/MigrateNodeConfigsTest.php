<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateSystemSiteTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

class MigrateNodeConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to node.settings.yml',
      'description'  => 'Upgrade variables to node.settings.yml',
      'group' => 'Migrate',
    );
  }

  function testNodeSettings() {
    $migration = entity_load('migration', 'd6_node_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6NodeSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
    $config = \Drupal::config('node.settings');
    $this->assertIdentical($config->get('use_admin_theme'), 0);
  }
}
