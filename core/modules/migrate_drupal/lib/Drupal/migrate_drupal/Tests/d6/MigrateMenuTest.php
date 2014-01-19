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

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate menus',
      'description'  => 'Upgrade menus to system.menu.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testMenu() {
    $migration = entity_load('migration', 'd6_menu');
      $dumps = array(
          drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Menu.php',
      );
      $this->prepare($migration, $dumps);
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();

      $navigation_menu = entity_load('menu', 'navigation');
      $this->assertEqual($navigation_menu->id, 'navigation');
      $this->assertEqual($navigation_menu->label, 'Navigation');
      $this->assertEqual($navigation_menu->description, 'The navigation menu is provided by Drupal and is the main interactive menu for any site. It is usually the only menu that contains personalized links for authenticated users, and is often not even visible to anonymous users.');
  }

}
