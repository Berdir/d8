<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6CommentSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Comment';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'source' => array(
      'plugin' => 'drupal6_comment',
    ),
    'sourceIds' => array(
      'cid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'c',
      ),
    ),
    'destinationIds' => array(
      'cid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.

  protected $expectedResults = array(
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

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 comment source functionality',
      'description' => 'Tests D6 comment source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->expectedResults as $k => $row) {
      // This array stores the database.
      $this->databaseContents['node'][$k]['nid'] = $row['nid'];
      $this->databaseContents['node'][$k]['type'] = $row['type'];
      unset($row['type']);
      $this->databaseContents['comments'][$k] = $row;
    }
    parent::setUp();
  }

}
