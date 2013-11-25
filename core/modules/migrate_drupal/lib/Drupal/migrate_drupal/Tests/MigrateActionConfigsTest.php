<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateActionConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateTestBase;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Action module.
 */
class MigrateActionConfigsTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to action.settings.yml',
      'description'  => 'Upgrade variables to action.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of action variables to action.settings.yml.
   */
  public function testActionSettings() {
    $migration = entity_load('migration', 'd6_action_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6ActionSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('action.settings');
    $this->assertIdentical($config->get('recursion_limit'), 35);
  }

}
