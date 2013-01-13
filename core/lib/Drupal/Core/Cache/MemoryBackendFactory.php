<?php

/**
 * @file
 * Contains Drupal\Core\Cache\MemoryBackendFactory.
 */

namespace Drupal\Core\Cache;

/**
 * Defines a factory for the cache memory backend.
 */
class MemoryBackendFactory {

  /**
   * Constructs a new cache database backend for a given bin name.
   *
   * @param string $bin
   *   The cache bin for which a cache backend object should be returned.
   *
   * @return Drupal\Core\Cache\CacheBackendInterface
   *   The cache backend object associated with the specified bin.
   */
  public function get($bin) {
    return new MemoryBackend($bin);
  }
}
