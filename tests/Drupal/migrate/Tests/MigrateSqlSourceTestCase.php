<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateSqlSourceTestCase.
 */

namespace Drupal\migrate\Tests;

/**
 * Provides setup and helper methods for Migrate module source tests.
 */
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

  /**
   * @var \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $database_contents = $this->databaseContents + array('test_map' => array());
    $database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
    $database->expects($this->any())->method('select')->will($this->returnCallback(function ($base_table, $base_alias) use ($database_contents) {
      return new FakeSelect($base_table, $base_alias, $database_contents);
    }));
    $database->expects($this->any())->method('schema')->will($this->returnCallback(function () use ($database, $database_contents) {
      return new FakeDatabaseSchema($database, $database_contents);
    }));
    $database->expects($this->any())->method('query')->will($this->throwException(new \Exception('Query is not supported')));
    $module_handler = $this->getMockBuilder('Drupal\Core\Extension\ModuleHandlerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $migration = $this->getMigration();
    $migration->expects($this->any())
      ->method('getHighwater')
      ->will($this->returnValue(static::ORIGINAL_HIGHWATER));
    // Need the test class, not the original because we need a setDatabase method. This is not pretty :/
    $plugin_class  = preg_replace('/^(Drupal\\\\\w+\\\\)Plugin\\\\migrate(\\\\source(\\\\.+)?\\\\)([^\\\\]+)$/', '\1Tests\2Test\4', static::PLUGIN_CLASS);
    $plugin = new $plugin_class($this->migrationConfiguration['source'], $this->migrationConfiguration['source']['plugin'], array(), $migration);
    $plugin->setDatabase($database);
    $plugin->setModuleHandler($module_handler);
    $migration->expects($this->any())
      ->method('getSourcePlugin')
      ->will($this->returnValue($plugin));
    $migrateExecutable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $this->source = new TestSource($migration, $migrateExecutable);

    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->source->setCache($cache);
  }

  /**
   * Tests retrieval.
   */
  public function testRetrieval() {
    $this->assertSame(count($this->results), count($this->source), 'Number of results match');
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

  /**
   * Asserts tested values during test retrieval.
   *
   * @param mixed $expected_value
   *   The incoming expected value to test.
   * @param mixed $actual_value
   *   The incoming value itself.
   * @param string $message
   *   The tested result as a formatted string.
   */
  protected function retrievalAssertHelper($expected_value, $actual_value, $message) {
    if (is_array($expected_value)) {
      foreach ($expected_value as $k => $v) {
        $this->retrievalAssertHelper($v, $actual_value[$k], $message . '['. $k . ']');
      }
    }
    else {
      $this->assertSame((string) $expected_value, (string) $actual_value, $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'SQL source test',
      'description' => 'Tests for SQL source plugin.',
      'group' => 'Migrate',
    );
  }

}
