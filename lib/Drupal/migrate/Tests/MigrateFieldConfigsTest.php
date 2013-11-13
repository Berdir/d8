<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateFieldConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Field module.
 */
class MigrateFieldConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to field.settings.yml',
      'description'  => 'Upgrade variables to field.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of field variables to field.settings.yml.
   */
  public function testFieldSettings() {
    $migration = entity_load('migration', 'd6_field_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6FieldSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('field.settings');
    $this->assertIdentical($config->get('language_fallback'), TRUE);
  }
}
