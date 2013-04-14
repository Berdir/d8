<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\MemoryCounterBackendFactory.
 */

namespace Drupal\Core\Cache;

class MemoryCounterBackendFactory {

  /**
   * List of instantiated cache backends.
   *
   * @var array
   */
  protected $backends = array();

  /**
   * Gets MemoryCounterBackend for the specified cache bin.
   *
   * @param $bin
   *   The cache bin for which the object is created.
   *
   * @return \Drupal\Core\Cache\MemoryCounterBackend
   *   The cache backend object for the specified cache bin.
   */
  function get($bin) {
    if (!isset($this->backends[$bin])) {
      $this->backends[$bin] = new MemoryCounterBackend($bin);
    }
    return $this->backends[$bin];
  }

}
