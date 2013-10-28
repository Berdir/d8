<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\ConfigDestinationTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Plugin\migrate\destination\Config;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group migrate
 */
class ConfigDestinationTest extends UnitTestCase {

  public function testImport() {
    $source = array(
      'test' => 'x',
    );
    $config = $this->getMockBuilder('Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('setData')
      ->with($this->equalTo($source))
      ->will($this->returnValue($config));
    $config->expects($this->once())
      ->method('save');
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $row->expects($this->once())
      ->method('getDestination')
      ->will($this->returnValue($source));
    $destination = new Config(array(), 'd8_config', array('pluginId' => 'd8_config'), $config);
    $destination->import($row);
  }

  /**
   * Provide meta information about this battery of tests.
   */
  public static function getInfo() {
    return array(
      'name' => 'Destination test',
      'description' => 'Tests for destination plugin.',
      'group' => 'Migrate',
    );
  }

}
