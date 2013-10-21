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

  protected $databaseContents = array();

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
    $query = new FakeSelect(static::BASE_TABLE, static::BASE_ALIAS, $this->databaseContents);

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
    $result_keys = array_keys($this->results[0]);
    $match_field = reset($result_keys);
    $count = 0;
    for ($this->source->rewind(); $this->source->valid(); $this->source->next()) {
      $data_row = $this->source->current();
      $match = FALSE;
      foreach ($this->results as $expected_row) {
        if ((string) $expected_row[$match_field] === (string) $data_row->getSourceProperty($match_field)) {
          $match = TRUE;
          $count++;
          foreach ($expected_row as $key => $expected_value) {
            $this->assertSame((string) $expected_value, (string) $data_row->getSourceProperty($key));
          }
          break;
        }
      }
      $this->assertTrue($match);
    }
    $this->assertSame(count($this->results), $count);
  }
}
