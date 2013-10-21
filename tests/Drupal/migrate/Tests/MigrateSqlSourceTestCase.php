<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlSourceTestCase.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Select;
use Drupal\Tests\UnitTestCase;

abstract class MigrateSqlSourceTestCase extends UnitTestCase {

  /**
   * The tested source plugin.
   *
   * @var \Drupal\migrate\Plugin\migrate\source\d6\Comment.
   */
  protected $source;

  protected $migrationConfiguration = array();

  protected $tableContents = array();

  protected $results = array();

  const PLUGIN_CLASS = '';

  const PLUGIN_ID = '';

  const BASE_TABLE = '';

  const BASE_ALIAS = '';

  /**
   * @var \Drupal\Core\Database\Query\Select
   */
  protected $query;

  protected function setUp() {
    $query = new FakeSelect(static::BASE_TABLE, static::BASE_ALIAS, $this->tableContents);

    $database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
    $database->expects($this->once())
      ->method('select')
      ->will($this->returnValue($query));

    $idmap = $this->getMock('Drupal\migrate\Plugin\MigrateIdMapInterface');
    $idmap->expects($this->once())
      ->method('getQualifiedMapTable')
      ->will($this->returnValue('test_map'));

    $migration = $this->getMock('Drupal\migrate\Entity\MigrationInterface');
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($idmap));
    $configuration = $this->migrationConfiguration;
    $migration->expects($this->any())
      ->method('get')
      ->will($this->returnCallback(function ($argument) use ($configuration) { return $configuration[$argument]; }));

    $configuration = array();
    $plugin_definition = array();
    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $key_value = $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface');
    $plugin_class = static::PLUGIN_CLASS;
    $this->source = new $plugin_class($configuration, static::PLUGIN_ID, $plugin_definition, $migration, $cache, $key_value);
    $reflection = new \ReflectionClass($this->source);
    $reflection_property = $reflection->getProperty('database');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($this->source, $database);
    $migration->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue($this->source));
  }


  /**
   * Tests retrieval.
   */
  public function testRetrieval() {
    $this->source->rewind();
    // First row.
    $this->assertTrue($this->source->valid(), 'Valid row found in source.');
    foreach ($this->results as $row) {
      $data_row = $this->source->current();
      foreach ($row as $key => $value) {
        $this->assertSame((string) $data_row[$key], (string) $value);
      }
      $this->source->next();
    }
    $this->assertFalse($this->source->valid(), 'Table size correct');
  }
}
