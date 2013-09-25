<?php

/**
 * @file
 * Contains Drupal\Core\KeyValueStore\StateInterface.
 */

namespace Drupal\Core\KeyValueStore;

/**
 * Provides the state system using a key value store.
 */
class State implements StateInterface {

  /**
   * The key value store to use.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * Static state cache.
   *
   * @var array
   */
  protected $cache = array();

  /**
   * Constructs a State object.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface $keyValueStore
   *  The key value store to use.
   */
  function __construct(KeyValueFactory $keyValueFactory) {
    $this->keyValueStore = $keyValueFactory->get('state');
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    $values = array();
    $load = array();
    foreach ($keys as $key) {
      // Check if we have a value in the cache.
      if (isset($this->cache[$key])) {
        $values[$key] = $this->cache[$key];
      }
      // Load the value if we don't have an explicit NULL value.
      elseif (!array_key_exists($key, $this->cache)) {
        $load[] = $key;
      }
    }

    if ($load) {
      $loaded_values = $this->keyValueStore->getMultiple($load);
      foreach ($load as $key) {
        if (isset($loaded_values[$key]) || array_key_exists($key, $loaded_values)) {
          $values[$key] = $loaded_values[$key];
          $this->cache[$key] = $loaded_values[$key];
        }
        else {
          $this->cache[$key] = NULL;
        }
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    $this->cache[$key] = $value;
    $this->keyValueStore->set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    foreach ($keys as $key) {
      unset($this->cache[$key]);
    }
    $this->keyValueStore->deleteMultiple($keys);
  }


  /**
   * {@inheritdoc}
   */
  public function get($key) {
    $values = $this->getMultiple(array($key));
    return isset($values[$key]) ? $values[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data) {
    foreach ($data as $key => $value) {
      $this->cache[$key] = $value;
    }
    $this->setMultiple($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    $this->deleteMultiple(array($key));
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->cache = array();
  }

}
