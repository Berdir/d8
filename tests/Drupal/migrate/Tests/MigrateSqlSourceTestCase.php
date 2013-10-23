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

  const ORIGINAL_HIGHWATER = '';

  /**
   * @var \Drupal\Core\Database\Query\Select
   */
  protected $query;

  protected function setUp() {
    $this->databaseContents += array('test_map' => array());
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
      ->will($this->returnCallback(function ($argument) use ($configuration) { return isset($configuration[$argument]) ? $configuration[$argument] : ''; }));
    $migration->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->migrationConfiguration['id']));

    $key_value = $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface');
    $key_value->expects($this->once())
      ->method('get')
      ->with($this->equalTo($this->migrationConfiguration['id']))
      ->will($this->returnValue(static::ORIGINAL_HIGHWATER));

    $configuration = array();
    $plugin_definition = array();
    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
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
    // TODO: make count() work in SOurceBase.
    // $this->assertSame(count($this->results), count($this->source));
    $count = 0;
    foreach ($this->source as $data_row) {
      $expected_row = $this->results[$count];
      $count++;
      foreach ($expected_row as $key => $expected_value) {
        $this->assertSame((string) $expected_value, (string) $data_row->getSourceProperty($key), $key);
      }
    }
    $this->assertSame(count($this->results), $count);
  }
}
