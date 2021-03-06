<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateSimpletestConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Simpletest module.
 */
class MigrateSimpletestConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to simpletest.settings.yml',
      'description'  => 'Upgrade variables to simpletest.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of simpletest variables to simpletest.settings.yml.
   */
  public function testSimpletestSettings() {
    $migration = entity_load('migration', 'd6_simpletest_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6SimpletestSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('simpletest.settings');
    $this->assertIdentical($config->get('clear_results'), TRUE);
    $this->assertIdentical($config->get('httpauth.method'), CURLAUTH_BASIC);
    $this->assertIdentical($config->get('httpauth.password'), NULL);
    $this->assertIdentical($config->get('httpauth.username'), NULL);
    $this->assertIdentical($config->get('verbose'), TRUE);
  }
}
