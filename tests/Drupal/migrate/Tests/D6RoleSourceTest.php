<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6RoleSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6RoleSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Role';

  // The source plugin ID.
  const PLUGIN_ID = 'drupal6_role';

  // The table passed to $this->database->select.
  const BASE_TABLE = 'role';

  // The base alias passed to $this->database->select.
  const BASE_ALIAS = 'r';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    // This needs to be the identifier of the actual key: rid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'rid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'r',
      ),
    ),
    'destinationIds' => array(
      'rid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.

  protected $results = array(
    array(
      'rid' => 1,
      'name' => 'anonymous user',
    ),
    array(
      'rid' => 2,
      'name' => 'authenticated user',
    ),
    array(
      'rid' => 4,
      'name' => 'example role 1',
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 role source functionality',
      'description' => 'Tests D6 role source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['role'][$k] = $row;
    }
    parent::setUp();
  }

}
