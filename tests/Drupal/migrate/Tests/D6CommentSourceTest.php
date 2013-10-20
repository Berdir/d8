<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6CommentSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Comment';

  const PLUGIN_ID = 'drupal6_comment';

  protected $migration_configuration = array(
      'id' => 'test',
      'highwaterProperty' => array('field' => 'test'),
      'idlist' => array(),
    );

  public static function getInfo() {
    return array(
      'name' => 'D6 comment source functionality',
      'description' => 'Tests D6 comment source plugin.',
      'group' => 'Migrate',
    );
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
