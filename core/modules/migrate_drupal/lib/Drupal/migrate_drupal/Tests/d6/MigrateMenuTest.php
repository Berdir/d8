<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateMenuTest
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateMenuTest extends MigrateDrupalTestBase {

  function testMenu() {
    $migration = entity_load('migration', 'd6_menu');
      $dumps = array(
          drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Menu.php',
      );
      $this->prepare($migration, $dumps);
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();

      $navigation_menu = entity_load('menu', 'navigation');
      $this->assertEqual($navigation_menu->menu_name, 'navigation');
      $this->assertEqual($navigation_menu->title, 'Navigation');
      $this->assertEqual($navigation_menu->description, 'Navigation description');
  }

}
