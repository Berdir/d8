<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\PropertyMapTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\process\PropertyMap;

/**
 * Tests for PropertyMap class.
 *
 * @group migrate
 */
class PropertyMapTest extends MigrateTestCase {

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

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'PropertyMap class functionality',
      'description' => 'Tests PropertyMap class functionality.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->migrateExecutable = $this->getMockBuilder('Drupal\migrate\MigrateExecutable')
      ->disableOriginalConstructor()
      ->getMock();
    $this->row = new Row($this->sourceIds, $this->values);
  }

  /**
   * Tests missing source default.
   */
  public function testMatchingSourceDefault() {
    $configuration = array(
      'destination' => 'testproperty',
      'default' => 'test',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty'], 'test');
  }

  /**
   * Tests missing source default.
   *
   * @expectedException \Drupal\migrate\MigrateException
   */
  public function testNoSourceDefaultProvided() {
    $configuration = array(
      'destination' => 'testproperty',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
  }

  /**
   * Tests source default sub-property.
   */
  public function testMatchingSourceDefaultProvidedDestinationSubproperty() {
    $configuration = array(
      'destination' => 'testproperty:testsubproperty',
      'default' => 'test',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty']['testsubproperty'], 'test');
  }

  /**
   * Tests source.
   */
  public function testSource() {
    $configuration = array(
      'source' => 'nid',
      'destination' => 'testproperty',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty'], 1);
  }

  /**
   * Tests source migration.
   */
  public function testSourceMigration() {
    $configuration = array(
      'source' => 'nid',
      'destination' => 'testproperty',
    );
    $map = new PropertyMap($configuration, 'property_map', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame($destination['testproperty'], 1);
  }

}
