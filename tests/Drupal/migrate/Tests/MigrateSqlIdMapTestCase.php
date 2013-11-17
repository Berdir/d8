<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlIdMapTestCase.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Row;

class MigrateSqlIdMapTestCase extends MigrateTestCase {

  /**
   * @var \Drupal\migrate\Plugin\MigrateIdMapInterface
   */
  protected $idMap;

  protected $mapJoinable = FALSE;

  protected $migrationConfiguration = array(
    'id' => 'sql_idmap_test',
    'sourceIds' => array(
      'source_id_property' => array(),
    ),
    'destinationIds' => array(
      'destination_id_property' => array(),
    ),
  );

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Get process plugin',
      'description' => 'Tests the get process plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    $migration = $this->getMigration();
    $this->database = $this->getDatabase(array());
    $this->idMap = new TestSqlIdmap($this->database, array(), 'sql', array(), $migration);
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($this->idMap));
  }

  /**
   * Test the id mapping method.
   *
   * Create two id mappings and update the second to verify that:
   *
   * - mergeon empty tables work.
   * - merge-insert on nonempty tables work.
   * - merge-update on nonempty tables work.
   */
  public function testSsaveIdMapping() {
    $source = array(
      'source_id_property' => 'source_value',
    );
    $row = new Row($source, array('source_id_property' => array()));
    $this->idMap->saveIdMapping($row, array('destination_id_property' => 2));
    $expected_defaults = array(
      'needs_update' => 0,
      'rollback_action' => 0,
      'hash' => '',
    );
    $expected_result = array(array(
      'sourceid1' => 'source_value',
      'destid1' => 2,
    ) + $expected_defaults);
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
    $source = array(
      'source_id_property' => 'source_value_1',
    );
    $row = new Row($source, array('source_id_property' => array()));
    $this->idMap->saveIdMapping($row, array('destination_id_property' => 3));
    $expected_result[] = array(
      'sourceid1' => 'source_value_1',
      'destid1' => 3,
    ) + $expected_defaults;
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
    $this->idMap->saveIdMapping($row, array('destination_id_property' => 4));
    $expected_result[1]['destid1'] = 4;
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
  }
}
