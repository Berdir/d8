<?php

/**
 * @file
 * Definition of Drupal\user\TempStoreFactory.
 */

namespace Drupal\user;

use Drupal\Core\Database\Connection;
use Drupal\Core\KeyValueStore\DatabaseStorageExpirable;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * Creates a key/value storage object for the current user or anonymous session.
 */
class TempStoreFactory {

  /**
   * The connection object used for this data.
   *
   * @var Drupal\Core\Database\Connection $connection
   */
  protected $connection;

  /**
   * The lock object used for this data.
   *
   * @var Drupal\Core\Lock\LockBackendInterface $lockBackend
   */
  protected $lockBackend;

  /**
   * Constructs a Drupal\user\TempStoreFactory object.
   *
   * @param Drupal\Core\Database\Connection $connection
   *   The connection object used for this data.
   * @param Drupal\Core\Lock\LockBackendInterface $lockBackend
   *   The lock object used for this data.
   */
  function __construct(Connection $connection, LockBackendInterface $lockBackend) {
    $this->connection = $connection;
    $this->lockBackend = $lockBackend;
  }

  /**
   * Creates a TempStore for the current user or anonymous session.
   *
   * The TempStore is owned by the currently authenticated user, or by the
   * active anonymous session if no user is logged in. The data is stored in
   * the database.
   *
   * @param string $namespace
   *   The namespace to use for this key/value store.
   *
   * @return Drupal\user\TempStore
   *   An instance of the the key/value store.
   */
  function get($namespace) {
    $storage = new DatabaseStorageExpirable($namespace, array('connection' => $this->connection));
    return new TempStore($storage, $this->lockBackend, $GLOBALS['user']->uid ?: session_id());
  }

}
