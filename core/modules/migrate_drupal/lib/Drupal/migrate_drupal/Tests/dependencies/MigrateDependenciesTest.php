<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateDependenciesTest.
 */

namespace Drupal\migrate_drupal\Tests\dependencies;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the migrate dependencies
 *
 * @group Drupal
 * @group migrate_drupal
 */
class MigrateDependenciesTest extends MigrateDrupalTestBase {

  static $modules = array('aggregator');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate dependency tests',
      'description'  => 'Ensure the consistency among the dependencies for migrate',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests that the order is correct when loading several migrations.
   */
  public function testMigrateDependenciesOrder() {
    $migration_items = array('d6_comment', 'd6_filter_format', 'd6_node');
    $migrations = entity_load_multiple('migration', $migration_items);
    $expected_order = array('d6_node', 'd6_filter_format', 'd6_comment');
    $this->assertEqual(array_keys($migrations), $expected_order, 'Migration dependencies order is correct.');
    $expected_dependencies = array('d6_node', 'd6_node_type', 'd6_filter_format');
    $this->assertEqual($migrations['d6_comment']->dependencies, drupal_map_assoc($expected_dependencies), 'Migration dependencies for comment include dependencies for node migration as well');
  }

  /**
   * Tests dependencies on the migration of aggregator feeds & items.
   */
  public function testAggregatorMigrateDependencies() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_aggregator_item');
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6AggregatorItem.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    try {
      $executable->import();
      $this->fail("The exception wasn't caught.");
    }
    catch (\Exception $e) {
      $this->pass("Migration aborted due to unmet dependencies.");
    }

  }
}
