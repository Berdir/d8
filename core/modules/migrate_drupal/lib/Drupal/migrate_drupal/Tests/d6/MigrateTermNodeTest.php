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
class MigrateTermNodeTest extends MigrateTermNodeTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate taxonomy term node',
      'description'  => 'Upgrade taxonomy term node associations',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test migrating taxonomy term node associations.
   */
  public function testTermNode() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migrations = entity_load_multiple('migration', array('d6_term_node:*'));
    foreach ($migrations as $migration) {
      $executable = new MigrateExecutable($migration, $this);
      $executable->import();
    }
    $nodes = node_load_multiple($this->nids, TRUE);
    $node = $nodes[$this->nids[1]];
    $this->assertEqual(count($node->test), 1);
    $this->assertEqual($node->test[0]->value, 1);
    $node = $nodes[$this->nids[2]];
    $this->assertEqual(count($node->test), 2);
    $this->assertEqual($node->test[0]->value, 2);
    $this->assertEqual($node->test[1]->value, 3);
  }
}
