<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\FieldSourceTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests Field migration from D6 to D8.
 *
 * @group migrate
 */
class FieldSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\Field';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test_field',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_field',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'field_name' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'f',
      ),
    ),
    'destinationIds' => array(
      'content_node_field' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.
  // These are sample result queries.
  protected $expectedResults = array(
    array(
      'field_name' => 'field_body',
      'type' => 'text',
      'global_settings' => '',
      'required' => 0,
      'multiple' => 0,
      'db_storage' => 1,
      'module' => 'text',
      'db_columns' => '',
      'active' => 1,
      'locked' => 0,
    ),
  );

  /**
   * Prepopulate contents with results.
   */
  public function setUp() {
    $this->expectedResults[0]['global_settings'] = array(
      'text_processing' => 0,
      'max_length' => '',
      'allowed_values' => '',
      'allowed_values_php' => '',
    );
    $this->expectedResults[0]['db_columns'] = array(
      'value' => array(
        'type' => 'text',
        'size' => 'big',
        'not null' => '',
        'sortable' => 1,
        'views' => 1,
      ),
    );
    $this->databaseContents['content_node_field'] = $this->expectedResults;
    $this->databaseContents['content_node_field'][0]['global_settings'] = serialize($this->databaseContents['content_node_field'][0]['global_settings']);
    $this->databaseContents['content_node_field'][0]['db_columns'] = serialize($this->databaseContents['content_node_field'][0]['db_columns']);

    $this->databaseContents['content_node_field_instance'][0]['widget_settings'] = serialize(array());
    $this->databaseContents['content_node_field_instance'][0]['widget_type'] = 'text_textarea';
    $this->databaseContents['content_node_field_instance'][0]['field_name'] = 'field_body';
    parent::setUp();
  }

  /**
   * Provide meta information about this battery of tests.
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 field source functionality',
      'description' => 'Tests D6 field source plugin.',
      'group' => 'Migrate',
    );
  }

}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\Field;

class TestField extends Field {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

}
