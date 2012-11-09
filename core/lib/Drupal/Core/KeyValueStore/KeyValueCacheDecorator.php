<?php

/**
 * @file
 * Contains Drupal\Core\KeyValueStore\KeyValueCacheDecorator.
 */

namespace Drupal\Core\KeyValueStore;

use Drupal\Core\Utility\CacheArray;

/**
 *
 */
class KeyValueCacheDecorator extends CacheArray implements KeyValueStoreInterface {

  /**
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface;
   */
  protected $keyValueStore;

  public function __construct(KeyValueFactory $keyValueFactory, $collection) {
    parent::__construct('state', 'cache');
    $this->keyValueStore = $keyValueFactory->get($collection);
  }

  protected function resolveCacheMiss($offset) {
    $this->storage[$offset] = $this->keyValueStore->get($offset);
  }

  public function get($key) {
    $this->offsetGet($key);
  }

  public function delete($key) {
    $this->offsetUnset($key);
    $this->keyValueStore->delete($key);
  }

  public function deleteMultiple(array $keys) {
    foreach ($keys as $key) {
      $this->offsetUnset($key);
    }
    $this->keyValueStore->deleteMulitple($keys);
  }

  public function getAll() {
    // Don't cache this.
    return $this->keyValueStore->getAll();
  }

  public function getCollectionName() {
    return $this->keyValueStore->getCollectionName();
  }

  public function getMultiple(array $keys) {
    $values = array();
    foreach ($keys as $key) {
      $values[$key] = $this->offsetGet($offset);
    }
    return $values;
  }

  public function setIfNotExists($key, $value) {
    if ($this->keyValueStore->setIfNotExists($key, $value)) {
      $this->offsetSet($key, $value);
    }
  }

  public function setMultiple(array $data) {
    $this->keyValueStore->setMultiple($data);
    foreach ($data as $key => $value) {
      $this->offsetSet($key, $value);
    }
  }

  public function set($key, $value) {
    $this->offsetSet($key, $value);
  }

}
