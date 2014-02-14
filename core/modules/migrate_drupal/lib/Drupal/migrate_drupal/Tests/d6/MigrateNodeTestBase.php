<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTestBase.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateNodeTestBase extends MigrateDrupalTestBase {

  public function setUp() {
    parent::setUp();
    $node_type = entity_create('node_type', array('type' => 'story'));
    $node_type->save();
    node_add_body_field($node_type);
    $id_mappings = array(
      'd6_node_type' => array(array(array('story'), array('story'))),
      'd6_filter_format' => array(
        array(array(1), array('restricted_html')),
        array(array(2), array('full_html')),
      ),
    );
    $this->prepareIdMappings($id_mappings);
    entity_create('field_entity', array(
      'entity_type' => 'node',
      'name' => 'field_test',
      'type' => 'text',
    ))->save();
    entity_create('field_instance', array(
      'entity_type' => 'node',
      'field_name' => 'field_test',
      'bundle' => 'story',
    ))->save();
    entity_create('field_entity', array(
      'entity_type' => 'node',
      'name' => 'field_test_two',
      'type' => 'integer',
      'cardinality' => -1,
    ))->save();
    entity_create('field_instance', array(
      'entity_type' => 'node',
      'field_name' => 'field_test_two',
      'bundle' => 'story',
    ))->save();
  }

}
