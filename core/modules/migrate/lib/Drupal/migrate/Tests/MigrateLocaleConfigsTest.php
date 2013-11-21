<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateLocaleConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Locale module.
 */
class MigrateLocaleConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to locale.settings.yml',
      'description'  => 'Upgrade variables to locale.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of locale variables to locale.settings.yml.
   */
  public function testLocaleSettings() {
    $migration = entity_load('migration', 'd6_locale_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6LocaleSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('locale.settings');
    $this->assertIdentical($config->get('cache_string'), 1);
    $this->assertIdentical($config->get('javascript.directory'), 'languages');
  }

}
