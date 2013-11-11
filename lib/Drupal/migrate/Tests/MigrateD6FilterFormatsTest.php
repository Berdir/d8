<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateD6FilterFormatsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

class MigrateD6FilterFormatsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to system.site.yml',
      'description'  => 'Upgrade variables to system.site.yml',
      'group' => 'Migrate',
    );
  }

  function testSystemSite() {
    $migration = entity_load('migration', 'd6_system_site');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/ib/Drupal/migrate/Tests/Dump/Drupal6FilterFormats.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
  }
}
