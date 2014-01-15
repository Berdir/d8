<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateBlockTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the block settings migration.
 */
class MigrateBlockTest extends MigrateDrupalTestBase {

  static $modules = array('block');

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
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_block');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Block.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $blocks = entity_load_multiple('block');
    $this->assertTrue(count($blocks));
  }
}
