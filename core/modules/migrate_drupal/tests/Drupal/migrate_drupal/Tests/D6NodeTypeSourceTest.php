<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6NodeTypeSourceTest.
 */

namespace Drupal\migrate_drupal\Tests;

/**
 * Tests Node Types migration from D6 to D8.
 *
 * @group migrate
 */
class D6NodeTypeSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\NodeType';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test_nodetypes',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_nodetype',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'type' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 't',
      ),
    ),
    'destinationIds' => array(
      'nodetype' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.
  // These are sample result queries.
  protected $expectedResults = array(
    array(
      'type' => 'page',
      'name' => 'Page',
      'module' => 'node',
      'description' => 'A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an "About us" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.',
      'help' => '',
      'has_title' => 1,
      'title_label' => 'Title',
      'has_body' => 1,
      'body_label' => 'Body',
      'min_word_count' => 0,
      'custom' => 1,
      'modified' => 0,
      'locked' => 0,
      'orig_type' => 'page',
    ),
    array(
      'type' => 'story',
      'name' => 'Story',
      'module' => 'node',
      'description' => 'A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.',
      'help' => '',
      'has_title' => 1,
      'title_label' => 'Title',
      'has_body' => 1,
      'body_label' => 'Body',
      'min_word_count' => 0,
      'custom' => 1,
      'modified' => 0,
      'locked' => 0,
      'orig_type' => 'story',
    ),
  );

  /**
   * Prepopulate contents with results.
   */
  public function setUp() {
    $this->databaseContents['node_type'] = $this->expectedResults;
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 node type source functionality',
      'description' => 'Tests D6 node type source plugin.',
      'group' => 'Migrate',
    );
  }

}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\d6\NodeType;

class TestNodeType extends NodeType {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
