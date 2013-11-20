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
  public function getIdMap($database_contents = array()) {
    $migration = $this->getMigration();
    $this->database = $this->getDatabase($database_contents);
    $id_map = new TestSqlIdmap($this->database, array(), 'sql', array(), $migration);
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($id_map));
    return $id_map;
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
  public function testSsaveIdMapping() {
    $source = array(
      'source_id_property' => 'source_value',
    );
    $row = new Row($source, array('source_id_property' => array()));
    $id_map = $this->getIdMap();
    $id_map->saveIdMapping($row, array('destination_id_property' => 2));
    $expected_defaults = array(
      'needs_update' => 0,
      'rollback_action' => 0,
      'hash' => '',
    );
    $expected_result = array(
      array(
        'sourceid1' => 'source_value',
        'destid1' => 2,
      ) + $expected_defaults,
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
    ) + $expected_defaults;
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

}
