<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateBookTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests the Drupal 6 book structure to Drupal 8 migration.
 */
class MigrateBookTest extends MigrateDrupalTestBase {

  public static $modules = array('book');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate book',
      'description'  => 'Upgrade book structure',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $id_mappings = array();
    for ($i = 4; $i <= 8; $i++) {
      $entity = entity_create('node', array(
        'type' => 'story',
        'nid' => $i,
      ));
      $entity->enforceIsNew();
      $entity->save();
      $id_mappings['d6_node'][] = array(array($i), array($i));
    }
    $this->prepareIdMappings($id_mappings);
    // Load database dumps to provide source data.
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Book.php',
    );
    $this->loadDumps($dumps);
    // Migrate books..
    $migration = entity_load('migration', 'd6_book');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests the Drupal 6 book structure to Drupal 8 migration.
   */
  public function testBook() {
    $nodes = node_load_multiple(array(4, 5, 6, 7, 8));
    $tree = \Drupal::service('book.manager')->bookTreeAllData(4);
    $this->assertTrue($tree, 'tree loaded');
  }

}
