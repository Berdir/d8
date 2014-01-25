<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\BlockSourceTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests Boxes migration from D6 to D8.
 *
 * @group migrate_drupal
 * @group Drupal
 */
class BlockSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\Block';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test',
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_block',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      // Block ID.
      'bid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'b',
      ),
    ),
    'destinationIds' => array(
      'id' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  /**
   * Sample block instance query results from the source.
   */
  protected $expectedResults = array(
    array(
      'bid' => 1,
      'module' => 'block',
      'delta' => '1',
      'theme' => 'garland',
      'status' => 1,
      'weight' => 0,
      'region' => 'left',
      'visibility' => 0,
      'pages' => '',
      'title' => 'Test Title 01',
      'cache' => -1,
    ),
    array(
      'bid' => 2,
      'module' => 'block',
      'delta' => '2',
      'theme' => 'garland',
      'status' => 1,
      'weight' => 5,
      'region' => 'right',
      'visibility' => 0,
      'pages' => '<front>',
      'title' => 'Test Title 02',
      'cache' => -1,
    ),
  );

  /**
   * Sample block roles table.
   */
  protected $expectedBlocksRoles = array(
    array(
      'module' => 'block',
      'delta' => 1,
      'rid' => 2,
    ),
  );

  /**
   * Prepopulate database contents.
   */
  public function setUp() {
    $this->databaseContents['blocks'] = $this->expectedResults;
    $this->databaseContents['blocks_roles'] = $this->expectedBlocksRoles;
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 block source functionality',
      'description' => 'Tests D6 block source plugin.',
      'group' => 'Migrate Drupal',
    );
  }
}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\Block;

class TestBlock extends Block {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
