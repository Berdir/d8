<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateNodeTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate nodes',
      'description'  => 'Node content migration',
      'group' => 'Migrate Drupal',
    );
  }

  function testNode() {

    $path = drupal_get_path('module', 'migrate_drupal');
//    $dumps = array(
//      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeType.php',
//    );
//    /** @var \Drupal\migrate\entity\Migration $node_type_migration */
//    $node_type_migration = entity_load('migration', 'd6_node_type');
//    $this->prepare($node_type_migration, $dumps);
//    $executable = new MigrateExecutable($node_type_migration , $this);
//    $executable->import();

    $id_mappings = array(
      'd6_node_type' => array(array(array('story'), array('story'))),
    );
    $this->prepareIdMappings($id_mappings);

    /** @var \Drupal\migrate\entity\Migration $migration */
    $migrations = entity_load_multiple('migration', array('d6_node:*'));

    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Node.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    foreach ($migrations as $migration) {
      $this->prepare($migration, $dumps);
      $executable = new MigrateExecutable($migration, $this);
      $executable->import();
    }

    $node = node_load(1);
    $this->assertEqual($node->id(), 1, 'Node 1 loaded.');
    $this->assertEqual($node->getType(), 'story', 'Node has the correct bundle.');
    $this->assertEqual($node->getTitle(), 'Test title', 'Node has the correct title.');
    $this->assertEqual($node->getCreatedTime(), 1388271197, 'Node has the correct created time.');
    $this->assertEqual($node->isSticky(), FALSE, 'Node has the correct sticky setting.');
    $this->assertEqual($node->getAuthorId(), 1, 'Node has the correct author id.');

//
//    $this->verbose(print_r($node, 1));
//    $this->assertEqual($node->field_test->value, 'This is a text field', "Single field storage field is correct.");
//    //$this->assertEqual($node->getRevisionCreationTime(), 1390095701, 'Node has the correct revision timestamp.');

  }

}
