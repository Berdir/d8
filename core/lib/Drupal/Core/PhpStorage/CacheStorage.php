<?php

/**
 * @file
 * Contains \Drupal\Core\PhpStorage\CacheStorage.
 */

namespace Drupal\Core\PhpStorage;

use Drupal\Component\PhpStorage\PhpStorageInterface;
use Drupal\Core\Cache\DatabaseBackend;
use Drupal\Core\Cache\DatabaseCacheTagsChecksum;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\StreamWrapperForCacheStorage;

/**
 * This class stores PHP classes in a cache storage keeping it opcacheable.
 */
class CacheStorage implements PhpStorageInterface {

  /**
   * The backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The configuration array passed to the constructor.
   *
   * @var array $configuration
   */
  protected $configuration;

  /**
   * @param array $configuration
   *   The configuration containing bin, secret and an optional callable
   *   cache_backend_factory.
   *
   * @see \Drupal\Core\PhpStorage\PhpStorageFactory::get()
   */
  public function __construct(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    $key = $this->getKeyFromFilename($name);
    $cids = [$key, "$key:mtime"];
    $this->cacheBackend()->getMultiple($cids);
    return !$cids;
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    $key = $this->getKeyFromFilename($name);
    $cached = $this->cacheBackend()->get("$key:mtime");
    if (!$cached) {
      return FALSE;
    }
    // Load the data from cache.
    // Hook in the fake phar wrapper. Opcode included in PHP 5.5 hardwires file
    // and phar as the only two stream wrappers which can be opcode cached.
    // The file protocol is used to read local files and will be triggered
    // multiple times by the classloader as the container class is loaded.
    // So for better performance use the phar protocol.
    stream_wrapper_unregister('phar');
    stream_wrapper_register('phar', 'Drupal\Core\StreamWrapper\StreamWrapperForCacheStorage');
    StreamWrapperForCacheStorage::init($this, $cached->data);
    $return = (include "phar://$name") !== FALSE;
    #var_dump(opcache_get_status(TRUE)['scripts']["phar://$name"]);
    // Restore the system wrapper.
    stream_wrapper_restore('phar');
    return $return;
  }

  /**
   * @param $name
   * @return bool|resource
   */
  public function open($name) {
    $key = $this->getKeyFromFilename(substr($name, 7));
    if (!$cached = $this->cacheBackend()->get($key)) {
      return FALSE;
    }
    // Copy it into a file in memory.
    if (!$handle = fopen('php://memory', 'rwb')) {
      return FALSE;
    }
    if (fwrite($handle, $cached->data) === FALSE || fseek($handle, 0) === -1) {
      fclose($handle);
      return FALSE;
    }
    return $handle;
  }

  /**
   * {@inheritdoc}
   */
  public function save($name, $code) {
    $key = $this->getKeyFromFilename($name);
    // We do not need a real mtime, we just need a timestamp that changes when
    // the code changes.
    $hash = hash('sha256', $code);
    $mtime = hexdec(substr($hash, 0, 7));
    $this->cacheBackend()->setMultiple([
      $key => ['data' => $code],
      "$key:mtime" => ['data' => $mtime],
    ]);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function writeable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    $return = $this->exists($name);
    $key = $this->getKeyFromFilename($name);
    // Delete nonetheless because between exists and delete the entry might've
    // been written.
    $this->cacheBackend()->delete($key);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->cacheBackend()->deleteAll();
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFullPath($name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    // Only PhpBackEnd::invalidateAll() uses this method and that's not
    // compatible anyways since it relies on getFullPath().
    throw new \BadMethodCallException('CacheStorage::listall() is not implemented.');
  }

  /**
   * @return \Drupal\Core\Cache\CacheBackendInterface
   */
  protected function cacheBackend() {
    if (!isset($this->cacheBackend)) {
      if (isset($this->configuration['cache_backend_factory'])) {
        $this->cacheBackend = call_user_func($this->configuration['cache_backend_factory'], $this->configuration);
      }
      else {
        $this->cacheBackend = static::getDatabaseBackend($this->configuration);
      }
    }
    return $this->cacheBackend;
  }

  /**
   * Construct a database cache backend.
   */
  protected static function getDatabaseBackend($configuration) {
    $connection = Database::getConnection();
    return new DatabaseBackend($connection, new DatabaseCacheTagsChecksum($connection), 'php_' . $configuration['bin']);
  }

  /**
   * Return a secret key based on the filename.
   *
   * By using a secret key, a SQL injection does not lead immediately to
   * arbitrary PHP inclusion.
   *
   * @param string $filename
   *   The filename.
   *
   * @return string
   *   The secret hash.
   */
  protected function getKeyFromFilename($filename) {
    return hash_hmac('sha256', $filename, $this->configuration['secret']);
  }

}
