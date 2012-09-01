<?php

/**
 * @file
 * Contains Drupal\Core\Config\CacheStorage.
 */

namespace Drupal\Core\Config;

use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Defines the Cache storage controller.
 *
 * This storage controller essentially just bridges the configuration system
 * storage to a backend of the cache system. All StorageInterface method calls
 * are forwarded to the corresponding CacheBackendInterface methods.
 *
 * @see Drupal\Core\Cache\CacheBackendInterface
 *
 * While this causes a level of indirection and the functionality could
 * technically be embedded into a configuration storage controller directly,
 * a separate bridge is architecturally cleaner and also allows for alternative
 * setups.
 *
 * By default, the configuration system uses this controller in the
 * CachedFileStorage implementation.
 *
 * @see Drupal\Core\Config\CachedFileStorage
 */
class CacheStorage implements StorageInterface {

  /**
   * Cache backend options for this storage controller.
   *
   * - backend: The cache backend to use.
   * - bin: The cache bin to use.
   *
   * @var array
   */
  protected $options;

  /**
   * The instantiated Cache backend.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $storage;

  /**
   * Implements Drupal\Core\Config\StorageInterface::__construct().
   */
  public function __construct(array $options = array()) {
    $options += array(
      'backend' => 'Drupal\Core\Cache\DatabaseBackend',
      'bin' => 'config',
    );
    $this->options = $options;
  }

  /**
   * Returns the instantiated Cache backend to use.
   */
  protected function getBackend() {
    if (!isset($this->storage)) {
      $this->storage = new $this->options['backend']($this->options['bin']);
    }
    return $this->storage;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::exists().
   */
  public function exists($name) {
    return (bool) $this->getBackend()->get($name);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::read().
   */
  public function read($name) {
    if ($cache = $this->getBackend()->get($name)) {
      // The cache backend supports primitive data types, but only an array
      // represents valid config object data.
      if (is_array($cache->data)) {
        return $cache->data;
      }
    }
    return FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::write().
   */
  public function write($name, array $data) {
    $this->getBackend()->set($name, $data, CacheBackendInterface::CACHE_PERMANENT, array('config' => array($name)));
    return TRUE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::delete().
   */
  public function delete($name) {
    $this->getBackend()->delete($name);
    return TRUE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::rename().
   */
  public function rename($name, $new_name) {
    $this->getBackend()->delete($name);
    $this->getBackend()->delete($new_name);
    return TRUE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::encode().
   */
  public static function encode($data) {
    return serialize($data);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   *
   * @throws ErrorException
   *   unserialize() triggers E_NOTICE if the string cannot be unserialized.
   */
  public static function decode($raw) {
    $data = @unserialize($raw);
    return is_array($data) ? $data : FALSE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::listAll().
   *
   * Not supported by CacheBackendInterface.
   */
  public function listAll($prefix = '') {
    return array();
  }
}
