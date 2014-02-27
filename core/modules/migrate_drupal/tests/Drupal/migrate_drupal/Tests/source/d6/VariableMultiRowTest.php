<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\VariableMultiRowTest.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests the Drupal 6 variable multirow source.
 *
 * @group migrate_drupal
 * @group Drupal
 */
class VariableMultiRowTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\VariableMultiRow';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    'id' => 'test',
    'idlist' => array(),
    'source' => array(
      'plugin' => 'd6_variable_multirow',
      'variables' => array(
        'foo',
        'bar',
      ),
    ),
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
  public function setDatabase(Connection $database) {
    $this->database = $database;
  }
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
