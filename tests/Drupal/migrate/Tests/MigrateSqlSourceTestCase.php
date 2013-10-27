<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlSourceTestCase.
 */

namespace Drupal\migrate\Tests;

abstract class MigrateSqlSourceTestCase extends MigrateTestCase {

  /**
   * The tested source plugin.
   *
   * @var \Drupal\migrate\Plugin\migrate\source\d6\Comment.
   */
  protected $source;

  protected $databaseContents = array();

  protected $results = array();

  const PLUGIN_CLASS = '';

  const ORIGINAL_HIGHWATER = '';

  protected function setUp() {
    $database_contents = $this->databaseContents + array('test_map' => array());
    $database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
    $database->expects($this->any())
      ->method('select')
      ->will($this->returnCallback(function ($base_table, $base_alias) use ($database_contents) { return new FakeSelect($base_table, $base_alias, $database_contents);}));
    $database->expects($this->any())
      ->method('schema')
      ->will($this->returnCallback(function () use ($database, $database_contents) { return new FakeDatabaseSchema($database, $database_contents);}));

    $key_value = $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface');
    $key_value->expects($this->once())
      ->method('get')
      ->with($this->equalTo($this->migrationConfiguration['id']))
      ->will($this->returnValue(static::ORIGINAL_HIGHWATER));

    $plugin_definition = array();
    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');

    $migration = $this->getMigration();

    $plugin_class = static::PLUGIN_CLASS;
    $this->source = new $plugin_class($this->migrationConfiguration['source'], $this->migrationConfiguration['source']['plugin'], $plugin_definition, $migration, $cache, $key_value);
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
    $this->assertSame(count($this->results), count($this->source));
    $count = 0;
    foreach ($this->source as $data_row) {
      $expected_row = $this->results[$count];
      $count++;
      foreach ($expected_row as $key => $expected_value) {
        $this->retrievalAssertHelper($expected_value, $data_row->getSourceProperty($key), sprintf('Value matches for key "%s"', $key));
      }
    }
    $this->assertSame(count($this->results), $count);
  }

  protected function retrievalAssertHelper($expected_value, $actual_value, $message) {
    if (is_array($expected_value)) {
      foreach ($expected_value as $k => $v) {
        $this->retrievalAssertHelper($v, $actual_value[$k], $message);
      }
    }
    else {
      $this->assertSame((string) $expected_value, (string) $actual_value, $message);
    }
  }

}
