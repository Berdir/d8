<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6FieldInstanceSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests Field Instances migration from D6 to D8.
 *
 * @group migrate
 */
class D6FieldInstanceSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\FieldInstance';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test_fieldinstance',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_fieldinstance',
    ),
    // This needs to be the identifier of the actual key: cid for comment, nid
    // for node and so on.
    'sourceIds' => array(
      'type_name' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'fi',
      ),
    ),
    'destinationIds' => array(
      'content_node_field_instance' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  // We need to set up the database contents; it's easier to do that below.
  // These are sample result queries.
  protected $expectedResults = array(
    array(
      'field_name' => 'field_body',
      'type_name' => 'page',
      'weight' => 1,
      'label' => 'body',
      'widget_type' => 'text_textarea',
      'widget_settings' => '',
      'display_settings' => '',
      'description' => '',
      'widget_module' => 'text',
      'widget_active' => 1,
    ),
  );

  /**
   * Prepopulate contents with results.
   */
  public function setUp() {
    $this->expectedResults[0]['widget_settings'] = array(
      'rows' => 5,
      'size' => 60,
      'default_value' => array(
        array(
          'value' => '',
          '_error_element' => 'default_value_widget][field_body][0][value',
          'default_value_php' => '',
        ),
      ),
    );
    $this->expectedResults[0]['display_settings'] = array(
      'label' => array(
        'format' => 'above',
        'exclude' => 0,
      ),
      'teaser' => array(
        'format' => 'default',
        'exclude' => 0,
      ),
      'full' => array(
        'format' => 'default',
        'exclude' => 0,
      ),
    );
    $this->databaseContents['content_node_field_instance'] = $this->expectedResults;
    $this->databaseContents['content_node_field_instance'][0]['widget_settings'] = serialize($this->databaseContents['content_node_field_instance'][0]['widget_settings']);
    $this->databaseContents['content_node_field_instance'][0]['display_settings'] = serialize($this->databaseContents['content_node_field_instance'][0]['display_settings']);
    parent::setUp();
  }

  /**
   * Provide meta information about this battery of tests.
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 field instance source functionality',
      'description' => 'Tests D6 field instance source plugin.',
      'group' => 'Migrate',
    );
  }

}
namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\d6\FieldInstance;

class TestFieldInstance extends FieldInstance {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
