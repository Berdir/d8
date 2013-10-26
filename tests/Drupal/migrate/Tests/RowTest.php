<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\RowTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for Row class.
 *
 * @group migrate
 */
class RowTest extends UnitTestCase {

  protected $test_source_ids = array(
    'nid' => 'Node ID',
  );
  protected $test_values = array(
    'nid' => 1,
    'title' => 'node 1',
  );
  protected $test_hash = '85795d4cde4a2425868b812cc88052ecd14fc912e7b9b4de45780f66750e8b1e';

  public static function getInfo() {
    return array(
      'name' => 'Row class functionality',
      'description' => 'Tests Row class functionality.',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests object creation.
   */
  public function testConstructor() {
    $row = new Row(array(), array());
    $this->assertSame(array(), $row->getSource(), 'Empty row');

    $row = new Row($this->test_source_ids, $this->test_values);
    $this->assertSame($this->test_values, $row->getSource(), 'Row with data, simple id.');

    $multi_source_ids = $this->test_source_ids + array('vid' => 'Node revision');
    $multi_source_ids_values = $this->test_values + array('vid' => 1);
    $row = new Row($multi_source_ids, $multi_source_ids_values);
    $this->assertSame($multi_source_ids_values, $row->getSource(), 'Row with data, multifield id.');

    $invalid_values = array(
      'title' => 'node X',
    );
    try {
      $row = new Row($this->test_source_ids, $invalid_values);
      $this->fail('Row with invalid data was created');
    }
    catch (\Exception $exception) {
      // Exception thrown correctly.
    }
  }

  /**
   * Tests hashing.
   */
  public function testHashing() {
    $row = new Row($this->test_source_ids, $this->test_values);
    $this->assertSame('', $row->getHash(), 'No hash at creation');
    $row->rehash();
    $this->assertSame($this->test_hash, $row->getHash(), 'Correct hash.');
    $row->rehash();
    $this->assertSame($this->test_hash, $row->getHash(), 'Correct hash even doing it twice.');
    $test_id_map = array(
      'original_hash' => '',
      'hash' => '',
      'needs_update' => MigrateIdMapInterface::STATUS_NEEDS_UPDATE,
    );
    $row->setIdMap($test_id_map);
    $row->rehash();
    $this->assertSame($this->test_hash, $row->getHash(), 'Correct hash even if id_mpa have changed.');
  }

}
