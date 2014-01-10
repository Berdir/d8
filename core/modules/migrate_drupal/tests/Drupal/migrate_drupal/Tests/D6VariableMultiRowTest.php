<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6VariableMultirowTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Unit test the multirow variable class.
 *
 * @group migrate
 * @group Drupal
 */
class D6VariableMultiRowTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\VariableMultiRow';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'highwaterProperty' => array('field' => 'test'),
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_variable_multirow',
      'variables' => array(
        'foo',
        'bar',
      ),
    ),
    'sourceIds' => array(
      'name' => array(
        'alias' => 'v',
      ),
    ),
    'destinationIds' => array(),
  );

  protected $expectedResults = array(
    array('name' => 'foo', 'value' => 1),
    array('name' => 'bar', 'value' => FALSE),
  );

  protected $databaseContents = array(
    'variable' => array(
      array('name' => 'foo', 'value' => 'i:1;'),
      array('name' => 'bar', 'value' => 'b:0;'),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 variable multirow source functionality',
      'description' => 'Tests D6 variable multirow source plugin.',
      'group' => 'Migrate Drupal',
    );
  }

}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\VariableMultiRow;

class TestVariableMultiRow extends VariableMultiRow {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
