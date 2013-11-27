<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Tests\process;

use Drupal\migrate\Plugin\migrate\process\Map;
use Drupal\migrate\Plugin\migrate\process\TestMap;

/**
 * @group migrate
 */
class MapTest extends MigrateProcessTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Map process plugin',
      'description' => 'Tests the map process plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    $this->row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $this->migrateExecutable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    parent::setUp();
  }

  /**
   * Test map when the source is a string.
   */
  function testMapWithSourceString() {
    $configuration['map']['foo'] = 'bar';
    $plugin = new Map($configuration, 'map', array());
    $value = $plugin->transform('foo', $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($value, 'bar');
  }

  /**
   * Test map when the source is a list.
   */
  function testMapWithSourceList() {
    $configuration['map']['foo']['bar'] = 'baz';
    $plugin = new Map($configuration, 'map', array());
    $value = $plugin->transform(array('foo', 'bar'), $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($value, 'baz');
  }
}
