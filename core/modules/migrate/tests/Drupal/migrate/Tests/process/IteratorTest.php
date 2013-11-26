<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Tests\process;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\migrate\process\Get;
use Drupal\migrate\Plugin\migrate\process\MigratationExecutableIteratorTest;
use Drupal\migrate\Plugin\migrate\process\TestIterator;
use Drupal\migrate\Row;
use Drupal\migrate\Tests\MigrateTestCase;

/**
 * @group migrate
 * @group Drupal
 */
class IteratorTest extends MigrateTestCase {

  /**
   * @var \Drupal\migrate\Plugin\migrate\process\TestIterator
   */
  protected $plugin;

  protected $migrationConfiguration = array(
    'id' => 'test',
  );

  protected $mapJoinable = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Iterator process plugin',
      'description' => 'Tests the iterator process plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    $this->plugin = new TestIterator();
    parent::setUp();
  }

  /**
   * Test the iterator process plugin.
   */
  function testIterator() {
    $migration = $this->getMigration();
    // Set up the properties for the iterator.
    $properties = array(
      'foo' => 'source_foo',
      'id' => 'source_id'
    );
    $this->plugin->setProcess($properties);
    // Manually create the plugins. Migration::getProcessPlugins does this
    // normally but the plugin system is not working.
    foreach ($properties as $destination => $source) {
      $iterator_plugins[$destination][] = new Get(array('source' => $source), 'get', array());
    }
    $migration->expects($this->at(1))
      ->method('getProcessPlugins')
      ->will($this->returnValue($iterator_plugins));
    // Set up the key for the iterator.
    $this->plugin->setKey('@id');
    $key_plugin['key'][] = new Get(array('source' => '@id'), 'get', array());
    $migration->expects($this->at(2))
      ->method('getProcessPlugins')
      ->will($this->returnValue($key_plugin));
    $migrate_executable = new MigrateExecutable($migration, $this->getMock('Drupal\migrate\MigrateMessageInterface'));
    // The current value of the pipeline.
    $current_value = array(
      array(
        'source_foo' => 'test',
        'source_id' => 42,
      ),
    );
    // This is not used but the interface requires it, so create an empty row.
    $row = new Row(array(), array());
    $new_value = $this->plugin->transform($current_value, $migrate_executable, $row, 'test');
    $this->assertSame(count($new_value), 1);
    $this->assertSame(count($new_value[42]), 2);
    $this->assertSame($new_value[42]['foo'], 'test');
    $this->assertSame($new_value[42]['id'], 42);
  }
}

namespace Drupal\migrate\Plugin\migrate\process;

class TestIterator extends Iterator {
  function __construct() {
  }
  function setKey($key) {
    $this->configuration['key'] = $key;
  }
  function setProcess($process) {
    $this->configuration['process'] = $process;
  }
}
