<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate\Tests;


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
  protected $connection;

  public static function getInfo() {
    return array(
      'name' => 'D6 comment source functionality',
      'description' => 'Tests D6 comment source plugin.',
      'group' => 'Migrate',
    );
  }

  protected function setUp() {
    $this->connection = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    // @todo Figure out how Comment::query() is used.
    // @todo create a StatementInterface object with relevant data attached? (or mocks)
    $statement = null;
    $this->connection->expects($this->any())
      ->method('query')
      ->will($this->returnValue($statement));

    $configuration = array();
    $plugin_definition = array();
    // @todo Instanciate a CacheBackendInterface object;
    $cache = null;
    $this->source = new Comment($configuration, 'drupal6_comment', $plugin_definition, $cache, $this->connection);
  }

  /**
   * Tests retrieval.
   */
  public function testRetrieval() {
    $source->rewind();
    // @todo mock two rows.
    $expected_data_keys = array('cid', 'pid', 'nid', 'uid', 'subject', 'comment', 'hostname', 'timestamp', 'status', 'thread', 'name', 'mail', 'homepage', 'format');
    // First row.
    $this->assertTrue($this->source->valid(), 'Valid row found in source.');
    $data_row = $source->current();
    foreach ($expected_data_keys as $expected_data_key) {
      $this->assertTrue(isset($data_row[$expected_data_key]), sprintf('Found key "%s" on source data row.', $expected_data_key));
    }
    // Second row.
    $data_row = $source->current();
    $source->next();
    foreach ($expected_data_keys as $expected_data_key) {
      $this->assertTrue(isset($data_row[$expected_data_key]), sprintf('Found key "%s" on source data row.', $expected_data_key));
    }
  }
}
