<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6ProfileFieldSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests Profile fields migration from D6 to D8.
 *
 * @group migrate
 */
class D6ProfileFieldsSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\ProfileField';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test_profile_fields',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_profile_field',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'fid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'pf',
      ),
    ),
    'destinationIds' => array(
      'profilefield' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.
  // These are sample result queries.
  //TODO: Add multiple cases
  protected $expectedResults = array(
    array(
      'fid' => 1,
      'title' => 'First name',
      'name' => 'profile_first_name',
      'explanation' => 'First name user',
      'category' => 'profile',
      'page' => '',
      'type' => 'textfield',
      'weight' => 0,
      'required' => 1,
      'register' => 0,
      'visibility' => 2,
      'autocomplete' => 0,
      'options' => '',
    ),
    array(
      'fid' => 2,
      'title' => 'Last name',
      'name' => 'profile_last_name',
      'explanation' => 'Last name user',
      'category' => 'profile',
      'page' => '',
      'type' => 'textfield',
      'weight' => 0,
      'required' => 0,
      'register' => 0,
      'visibility' => 2,
      'autocomplete' => 0,
      'options' => '',
    ),
    array(
      'fid' => 3,
      'title' => 'Policy',
      'name' => 'profile_policy',
      'explanation' => 'A checkbox that say if you accept policy of website',
      'category' => 'profile',
      'page' => '',
      'type' => 'checkbox',
      'weight' => 0,
      'required' => 1,
      'register' => 1,
      'visibility' => 2,
      'autocomplete' => 0,
      'options' => '',
    ),
  );

  /**
   * Prepopulate contents with results.
   */
  public function setUp() {
    $this->databaseContents['profile_fields'] = $this->expectedResults;
    parent::setUp();
  }

  /**
   * Provide meta information about this battery of tests.
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 profile field source functionality',
      'description' => 'Tests D6 profile field source plugin.',
      'group' => 'Migrate',
    );
  }

}

namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\d6\ProfileField;

class TestProfileField extends ProfileField {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
