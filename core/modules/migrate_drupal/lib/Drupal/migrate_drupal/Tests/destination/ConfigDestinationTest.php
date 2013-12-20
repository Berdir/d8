<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\ConfigDestinationTest.
 */

namespace Drupal\migrate_drupal\Tests\destination;

use Drupal\migrate\Plugin\migrate\destination\Config;
use Drupal\Tests\UnitTestCase;

/**
 * Test the raw config destination.
 *
 * @see \Drupal\migrate_drupal\Plugin\migrate\destination\Config
 * @group Drupal
 * @group migrate_drupal
 */
class ConfigDestinationTest extends UnitTestCase {

  /**
   * Test the import method.
   */
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
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Destination test',
      'description' => 'Tests for destination plugin.',
      'group' => 'Migrate',
    );
  }

}
