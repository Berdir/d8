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

  protected $testSourceIds = array(
    'nid' => 'Node ID',
  );
  protected $testValues = array(
    'nid' => 1,
    'title' => 'node 1',
  );
  protected $testHash = '85795d4cde4a2425868b812cc88052ecd14fc912e7b9b4de45780f66750e8b1e';
  // After changing title value to 'new title'.
  protected $testHashMod = '9476aab0b62b3f47342cc6530441432e5612dcba7ca84115bbab5cceaca1ecb3';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Row class functionality',
      'description' => 'Tests Row class functionality.',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests object creation: empty.
   */
  public function testRowWithoutData() {
    $row = new Row(array(), array());
    $this->assertSame(array(), $row->getSource(), 'Empty row');
  }

  /**
   * Tests object creation: basic.
   */
  public function testRowWithBasicData() {
    $row = new Row($this->testSourceIds, $this->testValues);
    $this->assertSame($this->testValues, $row->getSource(), 'Row with data, simple id.');
  }

  /**
   * Tests object creation: multiple source ids.
   */
  public function testRowWithMultipleSourceIds() {
    $multi_source_ids = $this->testSourceIds + array('vid' => 'Node revision');
    $multi_source_ids_values = $this->testValues + array('vid' => 1);
    $row = new Row($multi_source_ids, $multi_source_ids_values);
    $this->assertSame($multi_source_ids_values, $row->getSource(), 'Row with data, multifield id.');
  }

  /**
   * Tests object creation: invalid values.
   *
   * @expectedException Exception
   */
  public function testRowWithInvalidData() {
    $invalid_values = array(
      'title' => 'node X',
    );
    $row = new Row($this->testSourceIds, $invalid_values);
  }

  /**
   * Tests source inmutability after freeze.
   *
   * @expectedException Exception
   */
  public function testSourceFreeze() {
    $row = new Row($this->testSourceIds, $this->testValues);
    $row->rehash();
    $this->assertSame($this->testHash, $row->getHash(), 'Correct hash.');
    $row->setSourceProperty('title', 'new title');
    $row->rehash();
    $this->assertSame($this->testHashMod, $row->getHash(), 'Hash changed correctly.');
    $row->freezeSource();
    $row->setSourceProperty('title', 'new title');
  }

  /**
   * Tests hashing.
   */
  public function testHashing() {
    $row = new Row($this->testSourceIds, $this->testValues);
    $this->assertSame('', $row->getHash(), 'No hash at creation');
    $row->rehash();
    $this->assertSame($this->testHash, $row->getHash(), 'Correct hash.');
    $row->rehash();
    $this->assertSame($this->testHash, $row->getHash(), 'Correct hash even doing it twice.');
    $test_id_map = array(
      'original_hash' => '',
      'hash' => '',
      'needs_update' => MigrateIdMapInterface::STATUS_NEEDS_UPDATE,
    );
    $row->setIdMap($test_id_map);
    $row->rehash();
    $this->assertSame($this->testHash, $row->getHash(), 'Correct hash even if id_mpa have changed.');
    $row->setSourceProperty('title', 'new title');
    $row->rehash();
    $this->assertSame($this->testHashMod, $row->getHash(), 'Hash changed correctly.');
  }

}
