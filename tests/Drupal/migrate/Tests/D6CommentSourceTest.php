<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Select;
use Drupal\Tests\UnitTestCase;
use Drupal\migrate\Plugin\migrate\source\d6\Comment;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6CommentSourceTest extends UnitTestCase {
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

  public static function getInfo() {
    return array(
      'name' => 'D6 comment source functionality',
      'description' => 'Tests D6 comment source plugin.',
      'group' => 'Migrate',
    );
  }

  protected function setUp() {
    // The interface can't be mocked because of
    $statement = $this->getMock('Drupal\Core\Database\StatementEmpty');
    $statement->expects($this->exactly(2))
      ->method('valid')
      ->will($this->onConsecutiveCalls(TRUE, FALSE));
    $statement->expects($this->once())
      ->method('current')
      ->will($this->returnValue(array('whatever')));

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

    $migration = $this->getMock('Drupal\migrate\Entity\MigrationInterface');
    $migration->expects($this->any())
      ->method('getIdMap')
      ->will($this->returnValue($idmap));
    $configuration = array(
      'id' => 'test',
      'highwaterProperty' => array('field' => 'test'),
      'idlist' => array(),
    );
    $migration->expects($this->any())
      ->method('get')
      ->will($this->returnCallback(function ($argument) use ($configuration) { return $configuration[$argument]; }));
    $this->migration= $migration;

    $configuration = array();
    $plugin_definition = array();
    $cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $key_value = $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface');
    $this->source = new Comment($configuration, 'drupal6_comment', $plugin_definition, $migration, $cache, $key_value);
    $reflection = new \ReflectionClass($this->source);
    $reflection_property = $reflection->getProperty('database');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($this->source, $this->database);
  }


  /**
   * Tests retrieval.
   */
  public function testRetrieval() {
    $this->source->rewind();
    // @todo mock two rows.
    $expected_data_keys = array('cid', 'pid', 'nid', 'uid', 'subject', 'comment', 'hostname', 'timestamp', 'status', 'thread', 'name', 'mail', 'homepage', 'format');
    // First row.
    $this->assertTrue($this->source->valid(), 'Valid row found in source.');
    $data_row = $this->source->current();
    foreach ($expected_data_keys as $expected_data_key) {
      $this->assertTrue(isset($data_row[$expected_data_key]), sprintf('Found key "%s" on source data row.', $expected_data_key));
    }
    // Second row.
    $data_row = $this->source->current();
    $this->source->next();
    foreach ($expected_data_keys as $expected_data_key) {
      $this->assertTrue(isset($data_row[$expected_data_key]), sprintf('Found key "%s" on source data row.', $expected_data_key));
    }
  }
}
