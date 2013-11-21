<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Tests\process;

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

  function setUp() {
    $this->row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $this->migrateExecutable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $this->plugin = new TestMap();
    parent::setUp();
  }

  function testMap() {
    $map['foo']['bar'] = 'baz';
    $this->plugin->setMap($map);
    $value = $this->plugin->transform(array('foo', 'bar'), $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($value, 'baz');
  }
}


namespace Drupal\migrate\Plugin\migrate\process;

class TestMap extends Map {
  function __construct() {
  }
  function setMap($map) {
    $this->configuration['map'] = $map;
  }
}
