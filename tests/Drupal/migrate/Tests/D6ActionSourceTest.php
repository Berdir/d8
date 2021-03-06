<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6ActionSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests action migration from D6 to D8.
 *
 * @group migrate
 */
class D6ActionSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Action';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_action',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'aid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'a',
      ),
    ),
    'destinationIds' => array(
      'aid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.

  protected $expectedResults = array(
    array(
      'aid' => '1',
      'type' => 'system',
      'callback' => 'system_goto_action',
      'parameters' => 'a:1:{s:3:"url";s:4:"node";}',
      'description' => 'Redirect to node list page',
    ),
    array(
      'aid' => '2',
      'type' => 'system',
      'callback' => 'system_send_email_action',
      'parameters' => 'a:3:{s:9:"recipient";s:7:"%author";s:7:"subject";s:4:"Test";s:7:"message";s:4:"Test',
      'description' => 'Test notice email',
    ),
    array(
      'aid' => 'comment_publish_action',
      'type' => 'comment',
      'callback' => 'comment_publish_action',
      'parameters' => null,
      'description' => null,
    ),
    array(
      'aid' => 'node_publish_action',
      'type' => 'comment',
      'callback' => 'node_publish_action',
      'parameters' => null,
      'description' => null,
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 action source functionality',
      'description' => 'Tests D6 actions source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    $this->databaseContents['actions'] = $this->expectedResults;
    parent::setUp();
  }

}

namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\d6\Action;

class TestAction extends Action {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
