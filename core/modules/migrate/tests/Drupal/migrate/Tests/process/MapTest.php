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

  function testMap() {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $migrate_executable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $configuration['map']['foo']['bar'] = 'baz';
    $plugin = new Map($configuration, 'map', array());
    $value = $plugin->transform(array('foo', 'bar'), $migrate_executable, $row, 'destinationproperty');
    $this->assertSame($value, 'baz');
  }
}
