<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateBookConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Book module.
 */
class MigrateBookConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to book.settings.yml',
      'description'  => 'Upgrade variables to book.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of book variables to book.settings.yml.
   */
  public function testBookSettings() {
    $migration = entity_load('migration', 'd6_book_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6BookSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('book.settings');
    $this->assertIdentical($config->get('child_type'), 'book');
    $this->assertIdentical($config->get('block.navigation.mode'), 'all pages');
    $this->assertIdentical($config->get('allowed_types'), array('book'));
  }
}
