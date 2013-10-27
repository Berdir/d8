<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6MenuSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests menu migration from D6 to D8.
 *
 * @group migrate
 */
class D6MenuSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Menu';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'source' => array(
      'plugin' => 'drupal6_menu',
    ),
    'sourceIds' => array(
      'menu_name' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'm',
      ),
    ),
    'destinationIds' => array(
      'menu_name' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.

  protected $results = array(
    array(
      'menu_name' => 'menu-name-1',
      'title' => 'menu custom value 1',
      'description' => 'menu custom description value 1',
    ),
    array(
      'menu_name' => 'menu-name-2',
      'title' => 'menu custom value 2',
      'description' => 'menu custom description value 2',
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 menu source functionality',
      'description' => 'Tests D6 menu source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    // This array stores the database.
    foreach ($this->results as $k => $row) {
      $this->databaseContents['menu_custom'][$k] = $row;
    }
    parent::setUp();
  }

}
