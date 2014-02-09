<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTermNodeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of taxonomy terms.
 */
class MigrateTermNodeTest extends MigrateDrupalTestBase {

  static $modules = array('taxonomy');

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

  public function testTaxonomyTerms() {
    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'vid' => 'test',
    ));
    $vocabulary->save();
    $node_type = entity_create('node_type', array('type' => 'story'));
    $node_type->save();
    entity_create('field_entity', array(
      'name' => 'test',
      'entity_type' => 'node',
      'type' => 'taxonomy_term_reference',
      'cardinality' => -1,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $vocabulary->id(),
            'parent' => '0',
          ),
        ),
      )
    ))->save();
    entity_create('field_instance', array(
      'field_name' => 'test',
      'entity_type' => 'node',
      'bundle' => 'story',
    ))->save();
    $id_mappings = array(
      'd6_taxonomy_vocabulary' => array(
        array(array(1), array(1)),
      ),
      'd6_vocabulary_field' => array(
        array(array(1), array('node', 'test')),
      ),
    );
    $nids = array();
    for ($i = 1; $i <= 2; $i++) {
      $node = entity_create('node', array('type' => 'story'));
      $node->save();
      $nids[$i] = $node->id();
      $id_mappings['d6_node'][] = array(array($i), array($node->id()));
    }
    $this->prepareIdMappings($id_mappings);
    $path =drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Node.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TermNode.php',
    );
    $this->loadDumps($dumps);
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migrations = entity_load_multiple('migration', array('d6_term_node:*'));
    foreach ($migrations as $migration) {
      $executable = new MigrateExecutable($migration, $this);
      $executable->import();
    }
    $nodes = node_load_multiple($nids, TRUE);
    $node = $nodes[$nids[1]];
    $this->assertEqual(count($node->test), 1);
    $this->assertEqual($node->test[0]->value, 1);
    $node = $nodes[$nids[2]];
    $this->assertEqual(count($node->test), 2);
    $this->assertEqual($node->test[0]->value, 2);
    $this->assertEqual($node->test[1]->value, 3);
  }
}
