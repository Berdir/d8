<?php

/**
 * @file
 * Contains \Drupal\system\Tests\KeyValueStore\CacheDecoratorTest.
 */

namespace Drupal\system\Tests\KeyValueStore;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\KeyValueStore\KeyValueCacheDecorator;
use Drupal\Core\Lock\NullLockBackend;

/**
 * Tests the key-value keyvalue database storage.
 */
class CacheDecoratorTest extends StorageTestBase {

  /**
   * The cache backend in which the caches are stored.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  public static function getInfo() {
    return array(
      'name' => 'Key value cache decorator',
      'description' => 'Tests the key value cache decorator.',
      'group' => 'Key-value store',
    );
  }

  protected function setUp() {
    parent::setUp();
    $this->container
      ->register('keyvalue.memory', 'Drupal\Core\KeyValueStore\KeyValueMemoryFactory');
    global $conf;
    $conf['keyvalue_default'] = 'keyvalue.memory';
    $this->cache = new MemoryBackend('bin');
  }

  /**
   * Tests that values are cached.
   */
  public function testCache() {
    $stores = $this->createStorage();
    $values = array();
    // Set the value and test that it is correctly returned.
    foreach ($this->collections as $i => $collection) {
      $stores[$i]->set('key', $this->objects[$i]);
      $this->assertEqual($stores[$i]->get('key'), $this->objects[$i]);
      // Destruct the class to have it write the cache.
      $stores[$i]->destruct();

      // Delete the value from the key value storage.
      $this->container->get($this->factory)->get($collection)->delete('key');
    }

    // Create new objects.
    $stores = $this->createStorage();

    // Verify that we get the cached state as we have not notified the decorator
    // about the deletion.
    foreach ($this->collections as $i => $collection) {
      $this->assertEqual($stores[$i]->get('key'), $this->objects[$i]);

      // Reset the cache and make sure the value was updated.
      $stores[$i]->reset();
      $this->assertNull($stores[$i]->get('key'));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function createStorage() {
    $stores = array();
    // Prepare the memory key value storages and decorated ones.
    foreach ($this->collections as $i => $collection) {
      $stores[$i] = new KeyValueCacheDecorator($this->cache, new NullLockBackend(), $this->container->get($this->factory), $collection);
    }

    return $stores;
  }

}
