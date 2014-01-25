<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateBlockTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the block settings migration.
 */
class MigrateBlockTest extends MigrateDrupalTestBase {

  static $modules = array('block', 'views', 'comment', 'menu', 'custom_block');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate block settings to block.block.*.yml',
      'description'  => 'Upgrade block settings to block.block.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  public function testBlockMigration() {
    entity_create('menu', array('id' => 'primary-links'))->save();
    entity_create('menu', array('id' => 'secondary-links'))->save();
    entity_create('menu', array('id' => 'menu-test-menu'))->save();
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_block');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Block.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
    $blocks = entity_load_multiple('block');
    $this->assertTrue(count($blocks));
  }
}
