<?php

/**
 * @file
 * Contains Drupal\Core\Cache\DatabaseBackendFactory.
 */

namespace Drupal\Core\Cache;

use Drupal\Core\Database\Connection;

/**
 * Defines a factory for the cache database backend.
 */
class DatabaseBackendFactory {

  /**
   * Constructs this factory object.
   *
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection object containing the key-value tables.
   */
  function __construct(Connection $connection) {
    $this->connection = $connection;
  }

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
    return new DatabaseBackend($bin, $this->connection);
  }
}
