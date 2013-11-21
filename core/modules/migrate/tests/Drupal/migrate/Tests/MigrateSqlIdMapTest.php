<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlIdMapTestCase.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Row;

/**
 * @group migrate
 */
class MigrateSqlIdMapTest extends MigrateTestCase {

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
      'name' => 'Sql id map plugin',
      'description' => 'Tests the Sql id map plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * Creates a test sql id map plugin.
   *
   * @param array $database_contents
   *   An array keyed by table names. Value are list of rows where each row is
   *   an associative array of field => value.
   *
   * @return \Drupal\migrate\Tests\TestSqlIdmap
   *   A sql id map plugin test instance.
   */
  protected function getIdMap($database_contents = array()) {
    $migration = $this->getMigration();
    $this->database = $this->getDatabase($database_contents);
    $id_map = new TestSqlIdmap($this->database, array(), 'sql', array(), $migration);
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($id_map));
    return $id_map;
  }

  protected function idMapDefaults() {
    return array(
      'source_row_status' => 0,
      'rollback_action' => 0,
      'hash' => '',
    );
  }

  /**
   * Test the id mapping method.
   *
   * Create two id mappings and update the second to verify that:
   *
   * - saving new to empty tables work.
   * - saving new to nonempty tables work.
   * - updating work.
   */
  public function testSaveIdMapping() {
    $source = array(
      'source_id_property' => 'source_value',
    );
    $row = new Row($source, array('source_id_property' => array()));
    $id_map = $this->getIdMap();
    $id_map->saveIdMapping($row, array('destination_id_property' => 2));
    $expected_result = array(
      array(
        'sourceid1' => 'source_value',
        'destid1' => 2,
      ) + $this->idMapDefaults(),
    );
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
    $source = array(
      'source_id_property' => 'source_value_1',
    );
    $row = new Row($source, array('source_id_property' => array()));
    $id_map->saveIdMapping($row, array('destination_id_property' => 3));
    $expected_result[] = array(
      'sourceid1' => 'source_value_1',
      'destid1' => 3,
    ) + $this->idMapDefaults();
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
    $id_map->saveIdMapping($row, array('destination_id_property' => 4));
    $expected_result[1]['destid1'] = 4;
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_result);
  }

  /**
   * Tests the sql id map set message method.
   */
  public function testSetMessage() {
    $message = $this->getMock('Drupal\migrate\MigrateMessageInterface');
    $id_map = $this->getIdMap();
    $id_map->setMessage($message);
    $this->assertAttributeEquals($message, 'message', $id_map);
  }

  /**
   * Test the getRowBySource method when it succeeds.
   */
  public function testGetSourceByIdSucceeds() {
    $row = array(
      'sourceid1' => 'source_id_value_1',
      'sourceid2' => 'source_id_value_2',
      'destid1' => 'destination_id_value_1',
    ) + $this->idMapDefaults();
    $database_contents['migrate_map_sql_idmap_test'][] = $row;
    $row = array(
      'sourceid1' => 'source_id_value_3',
      'sourceid2' => 'source_id_value_4',
      'destid1' => 'destination_id_value_1',
    ) + $this->idMapDefaults();
    $database_contents['migrate_map_sql_idmap_test'][] = $row;
    $source_id_values = array($row['sourceid1'], $row['sourceid2']);
    $id_map = $this->getIdMap($database_contents);
    $result_row = $id_map->getRowBySource($source_id_values);
    $this->assertSame($row, $result_row);
  }

  /**
   * Test the destination ID lookup method.
   *
   * Scenarios to test (for both hits and misses) are:
   *
   * - Single-value source ID to single-value destination ID.
   * - Multi-value source ID to multi-value destination ID.
   * - Single-value source ID to multi-value destination ID.
   * - Multi-value source ID to single-value destination ID.
   */
  public function testLookupDestinationIDMapping() {
    $this->performLookupDestinationIDTest(1, 1);
    $this->performLookupDestinationIDTest(2, 2);
    $this->performLookupDestinationIDTest(1, 2);
    $this->performLookupDestinationIDTest(2, 1);
  }

  /**
   * Actually perform the destination ID test with a given number of source
   * and destination fields.
   *
   * @param $num_source_fields
   * @param $num_destination_fields
   */
  protected function performLookupDestinationIDTest($num_source_fields, $num_destination_fields) {
    // Adjust the migration configuration according to the number of source and
    // destination fields.
    $this->migrationConfiguration['sourceIds'] = array();
    $this->migrationConfiguration['destinationIds'] = array();
    $source_id_values = array();
    $nonexistent_id_values = array();
    $row = $this->idMapDefaults();
    for ($i = 1; $i <= $num_source_fields; $i++) {
      $row["sourceid$i"] = "source_id_value_$i";
      $source_id_values[] = "source_id_value_$i";
      $nonexistent_id_values[] = "nonexistent_source_id_value_$i";
      $this->migrationConfiguration['sourceIds']["source_id_property_$i"] = array();
    }
    $expected_result = array();
    for ($i = 1; $i <= $num_destination_fields; $i++) {
      $row["destid$i"] = "destination_id_value_$i";
      $expected_result[] = "destination_id_value_$i";
      $this->migrationConfiguration['destinationIds']["destination_id_property_$i"] = array();
    }
    $database_contents['migrate_map_sql_idmap_test'][] = $row;
    $id_map = $this->getIdMap($database_contents);
    // Test for a valid hit.
    $destination_id = array_values($id_map->lookupDestinationID($source_id_values));
    $this->assertSame($expected_result, $destination_id);
    // Test for a miss.
    $destination_id = $id_map->lookupDestinationID($nonexistent_id_values);
    $this->assertNull($destination_id);
  }

  /**
   * Test the source ID lookup method.
   *
   * Scenarios to test (for both hits and misses) are:
   *
   * - Single-value destination ID to single-value source ID.
   * - Multi-value destination ID to multi-value source ID.
   * - Single-value destination ID to multi-value source ID.
   * - Multi-value destination ID to single-value source ID.
   */
  public function testLookupSourceIDMapping() {
    $this->performLookupSourceIDTest(1, 1);
    $this->performLookupSourceIDTest(2, 2);
    $this->performLookupSourceIDTest(1, 2);
    $this->performLookupSourceIDTest(2, 1);
  }

  /**
   * Actually perform the source ID test with a given number of source
   * and destination fields.
   *
   * @param $num_source_fields
   * @param $num_destination_fields
   */
  protected function performLookupSourceIDTest($num_source_fields, $num_destination_fields) {
    // Adjust the migration configuration according to the number of source and
    // destination fields.
    $this->migrationConfiguration['sourceIds'] = array();
    $this->migrationConfiguration['destinationIds'] = array();
    $row = $this->idMapDefaults();
    $expected_result = array();
    for ($i = 1; $i <= $num_source_fields; $i++) {
      $row["sourceid$i"] = "source_id_value_$i";
      $expected_result[] = "source_id_value_$i";
      $this->migrationConfiguration['sourceIds']["source_id_property_$i"] = array();
    }
    $destination_id_values = array();
    $nonexistent_id_values = array();
    for ($i = 1; $i <= $num_destination_fields; $i++) {
      $row["destid$i"] = "destination_id_value_$i";
      $destination_id_values[] = "destination_id_value_$i";
      $nonexistent_id_values[] = "nonexistent_destination_id_value_$i";
      $this->migrationConfiguration['destinationIds']["destination_id_property_$i"] = array();
    }
    $database_contents['migrate_map_sql_idmap_test'][] = $row;
    $id_map = $this->getIdMap($database_contents);
    // Test for a valid hit.
    $source_id = array_values($id_map->lookupSourceID($destination_id_values));
    $this->assertSame($expected_result, $source_id);
    // Test for a miss.
    $source_id = $id_map->lookupSourceID($nonexistent_id_values);
    $this->assertNull($source_id);
  }
}
