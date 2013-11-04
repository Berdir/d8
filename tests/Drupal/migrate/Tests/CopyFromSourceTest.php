<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Tests;


use Drupal\migrate\Plugin\migrate\process\CopyFromSource;
use Drupal\migrate\Row;

/**
 * Tests for CopyFroumSource class.
 *
 * @group migrate
 * @group Drupal
 *
 * @covers \Drupal\migrate\Plugin\migrate\process\CopyFromSource
 */
class CopyFromSourceTest extends MigrateTestCase {

  protected $sourceIds = array(
    'nid' => 'Node ID',
  );
  protected $destinationIds = array(
    'nid' => 'Destination Id',
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
    $this->row = new Row($this->values, $this->sourceIds, $this->destinationIds);
  }

  /**
   * Tests sub destination.
   */
  public function testSubDestination() {
    $configuration = array(
      'nid' => 'testproperty:sub',
    );
    $map = new CopyFromSource($configuration, 'copy_from_source', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame(1, $destination['testproperty']['sub']);
  }

  /**
   * Tests missing source.
   */
  public function testNoSourceValue() {
    $configuration = array(
      'foo' => 'testproperty:sub',
    );
    $map = new CopyFromSource($configuration, 'copy_from_source', array());
    $map->apply($this->row, $this->migrateExecutable);
    $destination = $this->row->getDestination();
    $this->assertSame(array(), $destination);
  }

}
