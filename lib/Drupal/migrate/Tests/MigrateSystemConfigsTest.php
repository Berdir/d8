<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateSystemSiteTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

class MigrateSystemConfigsTest extends MigrateTestBase {

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
      drupal_get_path('module', 'migrate') . '/ib/Drupal/migrate/Tests/Dump/Drupal6SystemSite.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
    $config = \Drupal::config('system.site');
    $this->assertIdentical($config->get('name'), 'drupal');
    $this->assertIdentical($config->get('mail'), 'admin@example.com');
    $this->assertIdentical($config->get('slogan'), 'Migrate rocks');
    $this->assertIdentical($config->get('page.front'), 'anonymous-hp');
    $this->assertIdentical($config->get('page.403'), 'user');
    $this->assertIdentical($config->get('page.404'), 'page-not-found');
    $this->assertIdentical($config->get('weight_select_max'), 99);
    $this->assertIdentical($config->get('admin_compact_mode'), FALSE);
  }
}
