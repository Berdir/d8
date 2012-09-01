<?php

/**
 * @file
 * Contains Drupal\Core\Config\CachedFileStorage.
 */

namespace Drupal\Core\Config;

use Drupal\Core\Config\FileStorage;

/**
 * Storage controller that uses FileStorage as canonical storage and
 * CacheStorage as a cache.
 */
class CachedFileStorage implements StorageInterface {

  /**
   * The configuration options for the sub-storage controllers.
   *
   * An associative array whose keys are the class names of the storage
   * controllers to use, and whose values are the respective configuration
   * options for each storage controller.
   *
   * Two array elements are expected. A key and definition for
   * Drupal\Core\Config\FileStorage is required. The other element should point
   * to another storage controller that is used as cache.
   *
   * @var array
   */
  protected $options;

  /**
   * The instantiated sub-storage controllers.
   *
   * @var array
   */
  protected $storages = array();

  /**
   * Implements Drupal\Core\Config\StorageInterface::__construct().
   */
  public function __construct(array $options = array()) {
    $this->options = $options;

    $this->storages['file'] = new FileStorage($options['Drupal\Core\Config\FileStorage']);

    unset($options['Drupal\Core\Config\FileStorage']);
    list($cache_class, $cache_options) = each($options);
    $this->storages['cache'] = new $cache_class($cache_options);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::exists().
   */
  public function exists($name) {
    // A single filestat is faster than a complex cache lookup and possibly
    // subsequent filestat.
    return $this->storages['file']->exists($name);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::read().
   */
  public function read($name) {
    // Check the cache.
    $data = $this->storages['cache']->read($name);
    // If the cache returns no result, check the file storage.
    if ($data === FALSE) {
      $data = $this->storages['file']->read($name);
      // @todo Should the config object be cached if it does not exist?
      if ($data !== FALSE) {
        $this->storages['cache']->write($name, $data);
      }
    }
    return $data;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::write().
   */
  public function write($name, array $data) {
    $success = $this->storages['file']->write($name, $data);
    $this->storages['cache']->delete($name);
    return $success;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::delete().
   */
  public function delete($name) {
    $success = TRUE;
    foreach ($this->storages as $storage) {
      if (!$storage->delete($name)) {
        $success = FALSE;
      }
    }
    return $success;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::rename().
   */
  public function rename($name, $new_name) {
    $success = $this->storages['file']->rename($name, $new_name);
    $this->storages['cache']->rename($name, $new_name);
    return $success;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::encode().
   *
   * @todo Remove encode() from StorageInterface.
   */
  public static function encode($data) {
    return $this->storages['file']->encode($data);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   *
   * @todo Remove decode() from StorageInterface.
   */
  public static function decode($raw) {
    return $this->storages['file']->decode($raw);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::listAll().
   */
  public function listAll($prefix = '') {
    return $this->storages['file']->listAll($prefix);
  }
}
