<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\PropertyMapTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\process\PropertyMap;

/**
 * Tests for PropertyMap class.
 *
 * @group migrate
 */
class PropertyMapTest extends MigrateTestCase {

  protected $plugin_id;

  protected $plugin_definition;

  protected $sourceIds = array(
    'nid' => 'Node ID',
  );
  protected $values = array(
    'nid' => 1,
    'title' => 'node 1',
  );

  /**
   * @var \Drupal\migrate\MigrateExecutable
   */
  protected $migrateExecutable;

  /**
   * @var \Drupal\migrate\Row
   */
  protected $row;

  public static function getInfo() {
    return array(
      'name' => 'PropertyMap class functionality',
      'description' => 'Tests PropertyMap class functionality.',
      'group' => 'Migrate',
    );
  }

  protected function setUp() {
    $this->migrateExecutable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $this->row = new Row($this->sourceIds, $this->values);
  }

  public function testNoSourceDefaultProvided() {
    $configuration = array(
      'destination' => 'testproperty',
      'default' => 'test',
      'foo' => 'bar',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty']['values'], 'test');
    $this->assertSame($destination['testproperty']['configuration']['foo'], 'bar');
  }

  /**
   * @expectedException \Drupal\migrate\MigrateException
   */
  public function testNoSourceNoDefaultProvided() {
    $configuration = array(
      'destination' => 'testproperty',
      'foo' => 'bar',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
  }

  public function testNoSourceDefaultProvidedDestinationSubproperty() {
    $configuration = array(
      'destination' => 'testproperty:testsubproperty',
      'default' => 'test',
      'foo' => 'bar',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty']['testsubproperty']['values'], 'test');
    $this->assertSame($destination['testproperty']['testsubproperty']['configuration']['foo'], 'bar');
  }

  public function testSource() {
    $configuration = array(
      'source' => 'nid',
      'destination' => 'testproperty',
      'foo' => 'bar',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty']['values'], 1);
    $this->assertSame($destination['testproperty']['configuration']['foo'], 'bar');
  }

  public function testSourceMigration() {
    $configuration = array(
      'source' => 'nid',
      'destination' => 'testproperty',
      'foo' => 'bar',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty']['values'], 1);
    $this->assertSame($destination['testproperty']['configuration']['foo'], 'bar');
  }

}
