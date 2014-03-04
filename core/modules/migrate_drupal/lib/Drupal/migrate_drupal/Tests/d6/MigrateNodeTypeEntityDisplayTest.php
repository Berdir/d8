<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeEntityDisplayTeaser.
 */

namespace Drupal\migrate_drupal\Tests\d6;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests node body migrated into an entity display, default mode.
 */
class MigrateNodeTypeEntityDisplayTest extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  /**
   * The node types.
   *
   * @var array
   */
  protected $types = array('company', 'employee', 'sponsor');

  /**
   * The id of the migration being tested.
   *
   * @var string
   */
  protected $migrationId = 'd6_node_type_entity_display';

  /**
   * The view mode.
   *
   * @var string
   */
  protected $viewMode = 'default';

  /**
   * The expected contents of the component.
   *
   * @var array
   */
  protected $expectedComponent = array(
    'label' => 'hidden',
    'type' => 'text_default',
  );

  /**
   * The expected settings in the component.
   *
   * @var array
   */
  protected $expectedSettings = array();

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate node body into entity display,',
      'description'  => 'Upgrade node body settings to entity.display.node.*.default.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_field_instance' => array(
        array(array('fieldname', 'page'), array('node', 'fieldname', 'page')),
      ),
      'd6_node_type' => array(
        array(array('page'), array('page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    foreach ($this->types as $type) {
      entity_create('node_type', array('type' => $type))->save();
    }
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', $this->migrationId);
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeBodyInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Test the node type entity display migration.
   */
  public function testNodeTypeEntityDisplay() {
    foreach ($this->types as $type) {
      $component = entity_get_display('node', $type, $this->viewMode)->getComponent('body');
      foreach ($this->expectedComponent as $key => $value) {
        $this->assertEqual($component[$key], $value);
      }
      foreach ($this->expectedSettings as $key => $value) {
        $this->assertEqual($component['settings'][$key], $value);
      }
    }
  }

}
