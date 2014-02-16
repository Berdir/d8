<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeRevisionTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\Core\Database\Database;

/**
 * Test node revisions migration from Drupal 6 to 8.
 */
class MigrateNodeRevisionTest extends MigrateNodeTestBase {

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

  public function setUp() {
    parent::setUp();
    $id_mappings = array(
      'd6_node' => array(
        array(array(1), array(1)),
      ),
    );
    $this->prepareIdMappings($id_mappings);
    $node = entity_create('node', array(
      'type' => 'story',
      'nid' => 1,
      'vid' => 1,
    ));
    $node->enforceIsNew();
    $node->save();
  }

  public function testNode() {
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Node.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->loadDumps($dumps);
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migrations = entity_load_multiple('migration', array('d6_node_revision:*'));
    foreach ($migrations as $migration) {
      $executable = new MigrateExecutable($migration, $this);
      $executable->import();

      // This is required for the second import below.
      db_truncate($migration->getIdMap()->mapTableName())->execute();
    }

    $node = \Drupal::entityManager()->getStorageController('node')->loadRevision(2);
    $this->assertEqual($node->id(), 1, 'Node 1 loaded.');
    $this->assertEqual($node->getRevisionId(), 2, 'Node 1 revision 2loaded.');
    $this->assertEqual($node->body->value, 'test rev 2');
  }

}
