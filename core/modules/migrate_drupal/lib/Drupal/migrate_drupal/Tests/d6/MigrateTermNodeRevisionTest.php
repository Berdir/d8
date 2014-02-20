<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTermNodeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of taxonomy terms.
 */
class MigrateTermNodeRevisionTest extends MigrateTermNodeTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate taxonomy term node revisions',
      'description'  => 'Upgrade taxonomy term node associations',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $id_mappings = array(
      'd6_node_revision' => array(
        array(array(2), array(2)),
      ),
    );
    $this->prepareIdMappings($id_mappings);
  }

  /**
   * Test migrating taxonomy term node associations.
   */
  public function testTermRevisionNode() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migrations = entity_load_multiple('migration', array('d6_term_node_revision:*'));
    foreach ($migrations as $migration) {
      $executable = new MigrateExecutable($migration, $this);
      $executable->import();
    }
    $node = \Drupal::entityManager()->getStorageController('node')->loadRevision(2);
    $this->assertEqual(count($node->test), 2);
    $this->assertEqual($node->test[0]->value, 2);
    $this->assertEqual($node->test[1]->value, 4);
  }

}
