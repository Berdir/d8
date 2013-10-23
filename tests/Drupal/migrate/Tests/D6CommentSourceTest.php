<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6CommentSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Comment';

  const PLUGIN_ID = 'drupal6_comment';

  const BASE_TABLE = 'comment';

  const BASE_ALIAS = 'c';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'highwaterProperty' => array('field' => 'timestamp'),
    'idlist' => array(),
    'sourceKeys' => array(
      'cid' => array(
        // This is where the field schema would go.
      ),
    ),
    'destinationKeys' => array(
      'cid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $results = array(
    array(
      'cid' => 1,
      'pid' => 0,
      'nid' => 2,
      'uid' => 3,
      'subject' => 'subject value 1',
      'comment' => 'comment value 1',
      'hostname' => 'hostname value 1',
      'timestamp' => 1382255613,
      'status' => 1,
      'thread' => '',
      'name' => '',
      'mail' => '',
      'homepage' => '',
      'format' => 'testformat1',
      'type' => 'article',
    ),
    array(
      'cid' => 2,
      'pid' => 1,
      'nid' => 3,
      'uid' => 4,
      'subject' => 'subject value 2',
      'comment' => 'comment value 2',
      'hostname' => 'hostname value 2',
      'timestamp' => 1382255662,
      'status' => 1,
      'thread' => '',
      'name' => '',
      'mail' => '',
      'homepage' => '',
      'format' => 'testformat2',
      'type' => 'page',
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 comment source functionality',
      'description' => 'Tests D6 comment source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['node'][$k]['nid'] = $row['nid'];
      $this->databaseContents['node'][$k]['type'] = $row['type'];
      unset($row['type']);
      $this->databaseContents['comment'][$k] = $row;
    }
    parent::setUp();
  }

}
