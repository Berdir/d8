<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTermNodeTestBase.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateTermNodeTestBase extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  static $modules = array('node', 'taxonomy');

  public function setUp() {
    parent::setUp();
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
      'd6_node' => array(
        array(array(1), array(1)),
        array(array(2), array(2)),
      ),
    );
    $this->prepareIdMappings($id_mappings);
    $vids = array(1, 2, 3);
    for ($i = 1; $i <= 2; $i++) {
      $node = entity_create('node', array(
        'type' => 'story',
        'nid' => $i,
        'vid' => array_shift($vids),
      ));
      $node->enforceIsNew();
      $node->save();
      if ($i == 1) {
        $node->vid->value = array_shift($vids);
        $node->enforceIsNew(FALSE);
        $node->isDefaultRevision(FALSE);
        $node->save();
      }
    }
    $path =drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Node.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TermNode.php',
    );
    $this->loadDumps($dumps);
  }

}
