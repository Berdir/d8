<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateTextConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Text module.
 */
class MigrateTextConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to text.settings.yml',
      'description'  => 'Upgrade variables to text.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of text variables to text.settings.yml.
   */
  public function testTextSettings() {
    $migration = entity_load('migration', 'd6_text_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6TextSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('text.settings');
    $this->assertIdentical($config->get('default_summary_length'), 600);
  }

}
