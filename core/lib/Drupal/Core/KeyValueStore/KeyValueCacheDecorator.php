<?php

/**
 * @file
 * Contains \Drupal\Core\KeyValueStore\KeyValueCacheDecorator.
 */

namespace Drupal\Core\KeyValueStore;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheCollector;
use Drupal\Core\DestructableInterface;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * Provides a decorator for a key value store that caches all requested keys.
 */
class KeyValueCacheDecorator extends CacheCollector implements KeyValueStoreInterface, DestructableInterface {

  /**
   * The key value store to use.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface;
   */
  protected $keyValueStore;

  /**
   * Constructs a key value cache decorator.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to store the cache in.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock implementation to use when writing a changed cache storage.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactory $key_value_factory
   *   The key value factory to get the key value storage from.
   * @param string $collection
   *   Name of the key value storage collection, also used as the cache id.
   */
  public function __construct(CacheBackendInterface $cache, LockBackendInterface $lock, KeyValueFactory $key_value_factory, $collection) {
    parent::__construct($collection, $cache, $lock);
    $this->keyValueStore = $key_value_factory->get($collection);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveCacheMiss($offset) {
    $this->storage[$offset] = $this->keyValueStore->get($offset);
    $this->persist($offset);
    return $this->storage[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    parent::delete($key);
    $this->keyValueStore->delete($key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    foreach ($keys as $key) {
      $this->delete($key);
    }
    $this->keyValueStore->deleteMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    // Caching this would mean that the whole store is added to the cache,
    // this is expected to be a non-frequent operation that is not worth to be
    // loaded from cache.
    return $this->keyValueStore->getAll();
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() {
    return $this->keyValueStore->getCollectionName();
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    $values = array();
    foreach ($keys as $key) {
      $value = $this->get($key);
      // Only return keys with a value.
      if ($value !== NULL) {
        $values[$key] = $value;
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function setIfNotExists($key, $value) {
    if ($this->keyValueStore->setIfNotExists($key, $value)) {
      $this->set($key, $value);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data) {
    $this->keyValueStore->setMultiple($data);
    foreach ($data as $key => $value) {
      parent::set($key, $value);
      $this->persist($key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    $this->keyValueStore->set($key, $value);
    parent::set($key, $value);
    $this->persist($key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->keyValueStore->deleteAll();
    $this->clear();
  }

}
