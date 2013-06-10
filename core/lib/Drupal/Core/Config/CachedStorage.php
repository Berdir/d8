<?php

/**
 * @file
 * Contains Drupal\Core\Config\CachedStorage.
 */

namespace Drupal\Core\Config;

use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Defines the cached storage controller.
 *
 * The class gets another storage and a cache backend injected. It reads from
 * the cache and delegates the read to the storage on a cache miss. It also
 * handles cache invalidation.
 */
class CachedStorage implements StorageInterface, StorageCacheInterface {

  /**
   * The configuration storage to be cached.
   *
   * @var Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * The instantiated Cache backend.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * List of listAll() prefixes with their results.
   *
   * @var array
   */
  protected static $listAllCache = array();

  /**
   * Constructs a new CachedStorage controller.
   *
   * @param Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage controller to be cached.
   * @param Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend instance to use for caching.
   */
  public function __construct(StorageInterface $storage, CacheBackendInterface $cache) {
    $this->storage = $storage;
    $this->cache = $cache;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::exists().
   */
  public function exists($name) {
    // The cache would read in the entire data (instead of only checking whether
    // any data exists), and on a potential cache miss, an additional storage
    // lookup would have to happen, so check the storage directly.
    return $this->storage->exists($name);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::read().
   */
  public function read($name) {
    if ($cache = $this->cache->get($name)) {
      // The cache backend supports primitive data types, but only an array
      // represents valid config object data.
      if (is_array($cache->data)) {
        return $cache->data;
      }
    }
    // Read from the storage on a cache miss and cache the data, if any.
    $data = $this->storage->read($name);
    if ($data !== FALSE) {
      $this->cache->set($name, $data, CacheBackendInterface::CACHE_PERMANENT);
    }
    // If the cache contained bogus data and there is no data in the storage,
    // wipe the cache entry.
    elseif ($cache) {
      $this->cache->delete($name);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $remaining_names = $names;
    $list = array();
    $cached_list = $this->cache->getMultiple($remaining_names);

    // The cache backend removed names that were successfully loaded from the
    // cache.
    if (!empty($remaining_names)) {
      $list = $this->storage->readMultiple($remaining_names);
      // Cache configuration objects that were loaded from the storage.
      foreach ($list as $name => $data) {
        $this->cache->set($name, $data, CacheBackendInterface::CACHE_PERMANENT);
      }
    }

    // Add the configuration objects from the cache to the list.
    foreach ($cached_list as $name => $cache) {
      $list[$name] = $cache->data;
    }

    return $list;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::write().
   */
  public function write($name, array $data) {
    if ($this->storage->write($name, $data)) {
      // While not all written data is read back, setting the cache instead of
      // just deleting it avoids cache rebuild stampedes.
      $this->cache->set($name, $data, CacheBackendInterface::CACHE_PERMANENT);
      $this->cache->deleteTags(array('listAll' => TRUE));
      static::$listAllCache = array();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::delete().
   */
  public function delete($name) {
    // If the cache was the first to be deleted, another process might start
    // rebuilding the cache before the storage is gone.
    if ($this->storage->delete($name)) {
      $this->cache->delete($name);
      $this->cache->deleteTags(array('listAll' => TRUE));
      static::$listAllCache = array();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::rename().
   */
  public function rename($name, $new_name) {
    // If the cache was the first to be deleted, another process might start
    // rebuilding the cache before the storage is renamed.
    if ($this->storage->rename($name, $new_name)) {
      $this->cache->delete($name);
      $this->cache->delete($new_name);
      $this->cache->deleteTags(array('listAll' => TRUE));
      static::$listAllCache = array();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::encode().
   */
  public function encode($data) {
    return $this->storage->encode($data);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   */
  public function decode($raw) {
    return $this->storage->decode($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    // Do not cache when a prefix is not provided.
    if ($prefix) {
      return $this->findByPrefix($prefix);
    }
    return $this->storage->listAll();
  }

  /**
   * Finds configuration object names starting with a given prefix.
   *
   * Given the following configuration objects:
   * - node.type.article
   * - node.type.page
   *
   * Passing the prefix 'node.type.' will return an array containing the above
   * names.
   *
   * @param string $prefix
   *   The prefix to search for
   *
   * @return array
   *   An array containing matching configuration object names.
   */
  protected function findByPrefix($prefix) {
    if (!isset(static::$listAllCache[$prefix])) {
      // The : character is not allowed in config file names, so this can not
      // conflict.
      if ($cache = $this->cache->get('list:' . $prefix)) {
        static::$listAllCache[$prefix] = $cache->data;
      }
      else {
        static::$listAllCache[$prefix] = $this->storage->listAll($prefix);
        $this->cache->set('list:' . $prefix, static::$listAllCache[$prefix], CacheBackendInterface::CACHE_PERMANENT, array('listAll' => TRUE));
      }
    }
    return static::$listAllCache[$prefix];
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::deleteAll().
   */
  public function deleteAll($prefix = '') {
    // If the cache was the first to be deleted, another process might start
    // rebuilding the cache before the storage is renamed.
    $cids = $this->storage->listAll($prefix);
    if ($this->storage->deleteAll($prefix)) {
      $this->cache->deleteMultiple($cids);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Clears the static list cache.
   */
  public function resetListCache() {
    static::$listAllCache = array();
  }
}
