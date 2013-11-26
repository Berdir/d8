<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlIdMapTestCase.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

/**
 * Tests the sql based ID map implementation.
 *
 * @group Drupal
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

  /**
   * Sets defaults for sql id map plugin tests.
   */
  protected function idMapDefaults() {
    return array(
      'source_row_status' => MigrateIdMapInterface::STATUS_IMPORTED,
      'rollback_action' => MigrateIdMapInterface::ROLLBACK_DELETE,
      'hash' => '',
    );
  }

  /**
   * Tests the id mapping method.
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
   * Tests the getRowsNeedingUpdate method for rows that need an update.
   */
  public function testGetRowsNeedingUpdate() {
    $id_map = $this->getIdMap();
    $row_statuses = array(
      MigrateIdMapInterface::STATUS_IMPORTED,
      MigrateIdMapInterface::STATUS_NEEDS_UPDATE,
      MigrateIdMapInterface::STATUS_IGNORED,
      MigrateIdMapInterface::STATUS_FAILED,
    );
    // Create a mapping row for each STATUS constant.
    foreach ($row_statuses as $status) {
      $source = array('source_id_property' => 'source_value_' . $status);
      $row = new Row($source, array('source_id_property' => array()));
      $destination = array('destination_id_property' => 'destination_value_' . $status);
      $id_map->saveIdMapping($row, $destination, $status);
      $expected_results[] = array(
        'sourceid1' => 'source_value_' . $status,
        'destid1' => 'destination_value_' . $status,
        'source_row_status' => $status,
        'rollback_action' => MigrateIdMapInterface::ROLLBACK_DELETE,
        'hash' => '',
      );
      // Assert zero rows need an update.
      if ($status == MigrateIdMapInterface::STATUS_IMPORTED) {
        $rows_needing_update = $id_map->getRowsNeedingUpdate(1);
        $this->assertCount(0, $rows_needing_update);
      }
    }
    // Assert that test values exist.
    $this->queryResultTest($this->database->databaseContents['migrate_map_sql_idmap_test'], $expected_results);

    // Assert a single row needs an update.
    $row_needing_update = $id_map->getRowsNeedingUpdate(1);
    $this->assertCount(1, $row_needing_update);

    // Assert the row matches its original source.
    $source_id = $expected_results[MigrateIdMapInterface::STATUS_NEEDS_UPDATE]['sourceid1'];
    $test_row = $id_map->getRowBySource(array($source_id));
    $this->assertSame($test_row, $row_needing_update[0]);

    // Add additional row that needs an update.
    $source = array('source_id_property' => 'source_value_multiple');
    $row = new Row($source, array('source_id_property' => array()));
    $destination = array('destination_id_property' => 'destination_value_multiple');
    $id_map->saveIdMapping($row, $destination, MigrateIdMapInterface::STATUS_NEEDS_UPDATE);

    // Assert multiple rows need an update.
    $rows_needing_update = $id_map->getRowsNeedingUpdate(2);
    $this->assertCount(2, $rows_needing_update);
  }

  /**
   * Tests the sql id map message count method by counting and saving messages.
   */
  public function testMessageCount() {
    $message = 'Hello world.';
    $expected_results = array(0, 1, 2, 3);
    $id_map = $this->getIdMap();

    // Test count message multiple times starting from 0.
    foreach ($expected_results as $key => $expected_result) {
      $count = $id_map->messageCount();
      $this->assertEquals($expected_result, $count);
      $id_map->saveMessage(array($key), $message);
    }
  }

  /**
   * Tests the getRowBySource method when it succeeds.
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
   * Tests the destination ID lookup method.
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
   * Performs destination ID test on source and destination fields.
   *
   * @param int $num_source_fields
   *   Number of source fields to test.
   * @param int $num_destination_fields
   *   Number of destination fields to test.
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
    $destination_id = $id_map->lookupDestinationID($source_id_values);
    $this->assertSame($expected_result, $destination_id);
    // Test for a miss.
    $destination_id = $id_map->lookupDestinationID($nonexistent_id_values);
    $this->assertNull($destination_id);
  }

  /**
   * Tests the source ID lookup method.
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
   * Performs the source ID test on source and destination fields.
   *
   * @param int $num_source_fields
   *   Number of source fields to test.
   * @param int $num_destination_fields
   *   Number of destination fields to test.
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
    $source_id = $id_map->lookupSourceID($destination_id_values);
    $this->assertSame($expected_result, $source_id);
    // Test for a miss.
    $source_id = $id_map->lookupSourceID($nonexistent_id_values);
    $this->assertNull($source_id);
  }

  /**
   * Tests the update count method.
   *
   * Scenarios to test for:
   *
   * - No errors.
   * - One error.
   * - Multiple errors.
   */
  public function testUpdateCount() {
    $this->performUpdateCountTest(0);
    $this->performUpdateCountTest(1);
    $this->performUpdateCountTest(3);
  }

  /**
   * Performs the update count test with a given number of update rows.
   *
   * @param int $num_update_rows
   *   The number of update rows to test.
   */
  protected function performUpdateCountTest($num_update_rows) {
    $database_contents['migrate_map_sql_idmap_test'] = array();
    for ($i = 0; $i < 5; $i++) {
      $row = $this->idMapDefaults();
      $row['sourceid1'] = "source_id_value_$i";
      $row['destid1'] = "destination_id_value_$i";
      $row['source_row_status'] = MigrateIdMapInterface::STATUS_IMPORTED;
      $database_contents['migrate_map_sql_idmap_test'][] = $row;
    }
    for (; $i < 5 + $num_update_rows; $i++) {
      $row = $this->idMapDefaults();
      $row['sourceid1'] = "source_id_value_$i";
      $row['destid1'] = "destination_id_value_$i";
      $row['source_row_status'] = MigrateIdMapInterface::STATUS_NEEDS_UPDATE;
      $database_contents['migrate_map_sql_idmap_test'][] = $row;
    }
    $id_map = $this->getIdMap($database_contents);
    $count = $id_map->updateCount();
    $this->assertSame($num_update_rows, $count);
  }

  /**
   * Tests the error count method.
   *
   * Scenarios to test for:
   *
   * - No errors.
   * - One error.
   * - Multiple errors.
   */
  public function testErrorCount() {
    $this->performErrorCountTest(0);
    $this->performErrorCountTest(1);
    $this->performErrorCountTest(3);
  }

  /**
   * Performs error count test with a given number of error rows.
   *
   * @param int $num_error_rows
   *   Number of error rows to test.
   */
  protected function performErrorCountTest($num_error_rows) {
    $database_contents['migrate_map_sql_idmap_test'] = array();
    for ($i = 0; $i < 5; $i++) {
      $row = $this->idMapDefaults();
      $row['sourceid1'] = "source_id_value_$i";
      $row['destid1'] = "destination_id_value_$i";
      $row['source_row_status'] = MigrateIdMapInterface::STATUS_IMPORTED;
      $database_contents['migrate_map_sql_idmap_test'][] = $row;
    }
    for (; $i < 5 + $num_error_rows; $i++) {
      $row = $this->idMapDefaults();
      $row['sourceid1'] = "source_id_value_$i";
      $row['destid1'] = "destination_id_value_$i";
      $row['source_row_status'] = MigrateIdMapInterface::STATUS_FAILED;
      $database_contents['migrate_map_sql_idmap_test'][] = $row;
    }

    $id_map = $this->getIdMap($database_contents);
    $count = $id_map->errorCount();
    $this->assertSame($num_error_rows, $count);
  }

  /**
   * Tests the destroy method.
   *
   * Scenarios to test for:
   *
   * - No errors.
   * - One error.
   * - Multiple errors.
   */
  public function testDestroy() {
    $id_map = $this->getIdMap();
    $map_table_name = $id_map->getMapTableName();
    $message_table_name = $id_map->getMessageTableName();
    $row = new Row(array('source_id_property' => 'source_value'), array('source_id_property' => array()));
    $id_map->saveIdMapping($row, array('destination_id_property' => 2));
    $id_map->saveMessage(array('source_value'), 'A message');
    $this->assertTrue($this->database->schema()->tableExists($map_table_name),
                      "$map_table_name exists");
    $this->assertTrue($this->database->schema()->tableExists($message_table_name),
                      "$message_table_name exists");
    $id_map->destroy();
    $this->assertFalse($this->database->schema()->tableExists($map_table_name),
                       "$map_table_name does not exist");
    $this->assertFalse($this->database->schema()->tableExists($message_table_name),
                       "$message_table_name does not exist");
  }
}
