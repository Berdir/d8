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

  /**
   * Database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  protected $migration_configuration = array();

  const PLUGIN_CLASS = '';

  const PLUGIN_ID = '';

  protected function setUp() {
    // The interface can't be mocked because of
    $statement = $this->getMock('Drupal\Core\Database\StatementEmpty');

    $this->database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
    $this->database->expects($this->once())
      ->method('select')
      ->will($this->returnValue(new Select('comment', 'c', $this->database)));
    $this->database->expects($this->once())
      ->method('query')
      ->will($this->returnValue($statement));

    $idmap = $this->getMock('Drupal\migrate\Plugin\MigrateIdMapInterface');
    $idmap->expects($this->once())
      ->method('getQualifiedMapTable')
      ->will($this->returnValue('test_map'));
    $idmap->expects($this->exactly(3))
      ->method('getSourceKeys')
      ->will($this->returnValue(array(
        'test' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
    )));

    $migration = $this->getMock('Drupal\migrate\Entity\MigrationInterface');
    $migration->expects($this->exactly(2))
      ->method('getIdMap')
      ->will($this->returnValue($idmap));
    $configuration = $this->migration_configuration;
    $migration->expects($this->any())
      ->method('get')
      ->will($this->returnCallback(function ($argument) use ($configuration) { return $configuration[$argument]; }));
    $this->migration= $migration;

    $configuration = array();
    $plugin_definition = array();
    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $key_value = $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface');
    $plugin_class = static::PLUGIN_CLASS;
    $this->source = new $plugin_class($configuration, static::PLUGIN_ID, $plugin_definition, $migration, $cache, $key_value);
    $reflection = new \ReflectionClass($this->source);
    $reflection_property = $reflection->getProperty('database');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($this->source, $this->database);
  }

}
