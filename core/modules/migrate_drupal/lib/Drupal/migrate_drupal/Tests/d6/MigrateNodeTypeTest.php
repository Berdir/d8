<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests Drupal 6 node type to Drupal 8 migration.
 */
class MigrateNodeTypeTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate node type to node.type.*.yml',
      'description' => 'Upgrade node types to node.type.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $migration = entity_load('migration', 'd6_node_type');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeType.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests Drupal 6 node type to Drupal 8 migration.
   */
  public function testNodeType() {
    $migration = entity_load('migration', 'd6_node_type');
    // Test the migrate_test_page content type.
    $node_type_page = entity_load('node_type', 'migrate_test_page');
    $this->assertEqual($node_type_page->id(), 'migrate_test_page', 'Node type migrate_test_page loaded');
    $expected = array(
      'options' => array(
        'status' => TRUE,
        'promote' => TRUE,
        'sticky' => FALSE,
        'revision' => FALSE,
      ),
      'preview' => 1,
      'submitted' => TRUE,
    );
    $this->assertEqual($node_type_page->settings['node'], $expected, 'Node type migrate_test_page settings correct.');
    $this->assertEqual(array('migrate_test_page'), $migration->getIdMap()->lookupDestinationID(array('migrate_test_page')));

    // Test the migrate_test_story content type.
    $node_type_story = entity_load('node_type', 'migrate_test_story');
    $this->assertEqual($node_type_story->id(), 'migrate_test_story', 'Node type migrate_test_story loaded');
    $expected = array(
      'options' => array(
        'status' => TRUE,
        'promote' => TRUE,
        'sticky' => FALSE,
        'revision' => FALSE,
      ),
      'preview' => 1,
      'submitted' => TRUE,
    );
    $this->assertEqual($node_type_page->settings['node'], $expected, 'Node type migrate_test_page settings correct.');
    $this->assertEqual(array('migrate_test_story'), $migration->getIdMap()->lookupDestinationID(array('migrate_test_story')));
  }
}
