<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateD6FilterFormatsTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

class MigrateD6FilterFormatsTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to filter.formats.*.yml',
      'description'  => 'Upgrade variables to filter.formats.*.yml',
      'group' => 'Migrate',
    );
  }

  function testFilterFormats() {
    $migration = entity_load('migration', 'd6_filter_formats');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6FilterFormats.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
    $filter_format = entity_load('filter_format', 'filtered_html');
  }
}
