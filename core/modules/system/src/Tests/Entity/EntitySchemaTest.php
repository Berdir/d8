<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Entity\EntitySchemaTest.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\Component\Utility\String;

/**
 * Tests adding a custom bundle field.
 */
class EntitySchemaTest extends EntityUnitTestBase  {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The database connection used.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('menu_link');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Entity Schema',
      'description' => 'Tests entity field schema API for base and bundle fields.',
      'group' => 'Entity API',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installSchema('user', array('users_data'));
    $this->installSchema('system', array('router'));
    $this->moduleHandler = $this->container->get('module_handler');
    $this->database = $this->container->get('database');
  }

  /**
   * Tests the custom bundle field creation and deletion.
   */
  public function testCustomFieldCreateDelete() {
    // Install the module which adds the field.
    $this->installModule('entity_schema_test');
    $this->entityManager->clearCachedDefinitions();
    $definition = $this->entityManager->getBaseFieldDefinitions('entity_test')['custom_base_field'];
    $this->assertNotNull($definition, 'Base field definition found.');
    $definition = $this->entityManager->getFieldDefinitions('entity_test', 'custom')['custom_bundle_field'];
    $this->assertNotNull($definition, 'Bundle field definition found.');

    // Make sure the field schema has been created.
    /** @var \Drupal\Core\Entity\Sql\DefaultTableMappingInterface $table_mapping */
    $table_mapping = $this->entityManager->getStorage('entity_test')->getTableMapping();
    $base_table = current($table_mapping->getTableNames());
    $base_column = current($table_mapping->getColumnNames('custom_base_field'));
    $this->assertTrue($this->database->schema()->fieldExists($base_table, $base_column), 'Table column created');

    $table = $table_mapping->getDedicatedDataTableName($definition->getFieldStorageDefinition());
    $this->assertTrue($this->database->schema()->tableExists($table), 'Table created');
    $this->uninstallModule('entity_schema_test');
    $this->assertFalse($this->database->schema()->fieldExists($base_table, $base_column), 'Table column dropped');
    $this->assertFalse($this->database->schema()->tableExists($table), 'Table dropped');
  }

  /**
   * Tests that entity schema responds to changes in the entity type definition.
   */
  public function testEntitySchemaUpdate() {
    $this->installModule('entity_schema_test');
    $schema_handler = $this->database->schema();
    $tables = array('entity_test', 'entity_test_revision', 'entity_test_field_data', 'entity_test_field_revision');
    $dedicated_tables = array('entity_test__custom_bundle_field', 'entity_test_revision__custom_bundle_field');

    // Initially only the base table should exist.
    foreach ($tables as $index => $table) {
      $this->assertEqual($schema_handler->tableExists($table), !$index, String::format('Entity schema correct for the @table table.', array('@table' => $table)));
    }
    foreach ($dedicated_tables as $table) {
      $this->assertTrue($schema_handler->tableExists($table), String::format('Field schema correct for the @table table.', array('@table' => $table)));
    }

    // Update the entity type definition and check that the entity schema now
    // supports translations and revisions.
    $this->updateEntityType(TRUE);
    foreach ($tables as $table) {
      $this->assertTrue($schema_handler->tableExists($table), String::format('Entity schema correct for the @table table.', array('@table' => $table)));
    }
    foreach ($dedicated_tables as $table) {
      $this->assertTrue($schema_handler->tableExists($table), String::format('Field schema correct for the @table table.', array('@table' => $table)));
    }

    // Revert changes and check that the entity schema now does not support
    // neither translations nor revisions.
    $this->updateEntityType(FALSE);
    foreach ($tables as $index => $table) {
      $this->assertEqual($schema_handler->tableExists($table), !$index, String::format('Entity schema correct for the @table table.', array('@table' => $table)));
    }
    foreach ($dedicated_tables as $table) {
      $this->assertTrue($schema_handler->tableExists($table), String::format('Field schema correct for the @table table.', array('@table' => $table)));
    }
  }

  /**
   * Updates the entity type definition.
   *
   * @param bool $alter
   *   Whether the original definition should be altered or not.
   */
  protected function updateEntityType($alter) {
    $entity_test_id = 'entity_test';
    $original = $this->entityManager->getDefinition($entity_test_id);
    $this->entityManager->clearCachedDefinitions();
    $this->state->set('entity_schema_update', $alter);
    $this->entityManager->getStorage($entity_test_id)->onEntityDefinitionUpdate($original);
  }

  /**
   * Installs a module and refreshes services.
   *
   * @param string $module
   *   The module to install.
   */
  protected function installModule($module) {
    $this->moduleHandler->install(array($module), FALSE);
    $this->refreshServices();
  }

  /**
   * Uninstalls a module and refreshes services.
   *
   * @param string $module
   *   The module to uninstall.
   */
  protected function uninstallModule($module) {
    $this->moduleHandler->uninstall(array($module), FALSE);
    $this->refreshServices();
  }

  /**
   * Refresh services.
   */
  protected function refreshServices() {
    $this->container = \Drupal::getContainer();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->database = $this->container->get('database');
    $this->entityManager = $this->container->get('entity.manager');
    $this->state = $this->container->get('state');
  }

}
