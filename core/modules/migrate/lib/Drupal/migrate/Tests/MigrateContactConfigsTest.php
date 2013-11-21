<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateContactConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Contact module.
 */
class MigrateContactConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to contact.settings',
      'description'  => 'Upgrade variables to contact.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of aggregator variables to aggregator.settings.yml.
   */
  public function testContactSettings() {
    $migration = entity_load('migration', 'd6_contact_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6ContactSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('contact.settings');
    $this->assertIdentical($config->get('user_default_enabled'), 1);
    $this->assertIdentical($config->get('flood.limit'), 3);
    $this->assertIdentical($config->get('flood.interval'), 3600);
  }
}
