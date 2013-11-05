<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6BoxesSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests Boxes migration from D6 to D8.
 *
 * @group migrate
 */
class D6BoxesSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Boxes';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_boxes',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      // Box (block) ID.
      'bid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'b',
      ),
    ),
    'destinationIds' => array(
      'entity_id' => array(
        // This is where the field schema would go.
      ),
      'deleted' => array(),
      'delta' => array(),
      'langcode' => array(),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.
  // These are sample result queries.
  protected $results = array(
    array(
      'bid' => 1,
      'body' => '<p>I made some custom content.</p>',
      'info' => 'Static Block',
      'format' => 1,
    ),
    array(
      'bid' => 2,
      'body' => '<p>I made some more custom content.</p>',
      'info' => 'Test Content',
      'format' => 1,
    ),
  );

  /**
   * Prepopulate contents with results.
   */
  public function setUp() {
    $this->databaseContents['boxes'] = $this->results;
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 block boxes source functionality',
      'description' => 'Tests D6 block boxes source plugin.',
      'group' => 'Migrate',
    );
  }

}

namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\migrate\Plugin\migrate\source\d6\Boxes;

class TestBoxes extends Boxes {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
}
