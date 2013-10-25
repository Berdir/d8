<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6NodeSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests node migration from D6 to D8.
 *
 * @group migrate
 */
class D6NodeSourceNoFieldsTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Node';
  const PLUGIN_ID = 'drupal6_node';
  const BASE_TABLE = 'node';
  const BASE_ALIAS = 'n';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'sourceIds' => array(
      'nid' => array(
        'alias' => 'n',
      ),
    ),
    'destinationIds' => array(
      'nid' => array(
        // This is where the field schema would go.
      ),
    ),
  );
  // The fake configuration for the source.
  protected $sourceConfiguration = array(
    'node_type' => 'page',
  );

  protected $results = array(
    array(
      // Node fields.
      'nid' => 1,
      'vid' => 1,
      'type' => 'page',
      'language' => 'en',
      'title' => 'node title 1',
      'uid' => 1,
      'status' => 1,
      'created' => 1279051598,
      'changed' => 1279051598,
      'comment' => 2,
      'promote' => 1,
      'moderate' => 0,
      'sticky' => 0,
      'tnid' => 0,
      'translate' => 0,
      // Node revision fields.
      'body' => 'body for node 1',
      'teaser' => 'body for node 1',
      'format' => 1,
    ),
    array(
      // Node fields.
      'nid' => 3,
      'vid' => 3,
      'type' => 'page',
      'language' => 'en',
      'title' => 'node title 2',
      'uid' => 1,
      'status' => 1,
      'created' => 1279290908,
      'changed' => 1279308993,
      'comment' => 0,
      'promote' => 1,
      'moderate' => 0,
      'sticky' => 0,
      'tnid' => 0,
      'translate' => 0,
      // Node revision fields.
      'body' => 'body for node 3',
      'teaser' => 'body for node 3',
      'format' => 1,
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 node source functionality',
      'description' => 'Tests D6 node source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['node_revisions'][$k]['nid'] = $row['nid'];
      $this->databaseContents['node_revisions'][$k]['vid'] = $row['vid'];
      $this->databaseContents['node_revisions'][$k]['body'] = $row['body'];
      $this->databaseContents['node_revisions'][$k]['teaser'] = $row['teaser'];
      $this->databaseContents['node_revisions'][$k]['format'] = $row['format'];
      unset($row['body']);
      unset($row['teaser']);
      unset($row['format']);
      $this->databaseContents['node'][$k] = $row;
      $this->databaseContents['node'][$k] = $row;
    }
    parent::setUp();
  }

}
