<?php

/**
 * @file
 * Contains \Drupal\config\Tests\Storage\MemoryStorageTest.
 */

namespace Drupal\config\Tests\Storage;

use Drupal\Core\Config\MemoryStorage;

/**
 * Tests the memory config storage implementation.
 *
 * @see \Drupal\Core\Config\MemoryStorage
 */
class MemoryStorageTest extends ConfigStorageTestBase {

  /**
   * Stores the actual config data for the test.
   *
   * @var array
   */
  protected $array;

  /**
   * The config storage used in the test.
   *
   * @var \Drupal\Core\Config\MemoryStorage
   */
  protected $storage;

  /**
   * The invalid config storage used in the test.
   *
   * @var \Drupal\Core\Config\MemoryStorage
   */
  protected $invalidStorage;

  public static function getInfo() {
    return array(
      'name' => 'Memory storage config',
      'description' => 'Tests the memory config storage implementation.',
      'group' => 'Configuration',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->array = array();
    $this->storage = new MemoryStorage($this->array);

    // ::listAll() verifications require other configuration data to exist.
    $this->storage->write('system.performance', array());

    $empty_array = array(NULL);
    $this->invalidStorage = new MemoryStorage($empty_array);
  }

  /**
   * {@inheritdoc}
   */
  protected function read($name) {
    return isset($this->array[$name]) ? unserialize($this->array[$name]) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function insert($name, $data) {
    $this->array[$name] = serialize($data);
  }

  /**
   * {@inheritdoc}
   */
  protected function update($name, $data) {
    $this->array[$name] = serialize($data);
  }

  /**
   * {@inheritdoc}
   */
  protected function delete($name) {
    unset($this->array[$name]);
  }

}
