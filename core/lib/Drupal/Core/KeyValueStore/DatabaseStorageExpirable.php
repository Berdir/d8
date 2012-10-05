<?php

/**
 * @file
 * Contains Drupal\Core\KeyValueStore\DatabaseStorageExpirable.
 */

namespace Drupal\Core\KeyValueStore;

use Drupal\Core\Database\Query\Merge;

/**
 * Defines a default key/value store implementation for expiring items.
 *
 * This is Drupal's default key/value store implementation. It uses the database
 * to store key/value data with an expire date.
 */
class DatabaseStorageExpirable extends DatabaseStorage implements KeyValueStoreExpirableInterface {

  /**
   * The connection object for this storage.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Overrides Drupal\Core\KeyValueStore\StorageBase::__construct().
   *
   * @param string $collection
   *   The name of the collection holding key and value pairs.
   * @param array $options
   *   An associative array of options for the key/value storage collection.
   *   Keys used:
   *   - table. The name of the SQL table to use, defaults to key_value_expire.
   */
  public function __construct($collection, array $options = array()) {
    parent::__construct($collection, $options);
    $this->connection = $options['connection'];
    $this->table = isset($options['table']) ? $options['table'] : 'key_value_expire';
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreInterface::getMultiple().
   */
  public function getMultiple(array $keys) {
    $values = array();
    try {
      $result = $this->connection->query('SELECT name, value, expire FROM {' . $this->connection->escapeTable($this->table) . '} WHERE expire > :now AND name IN (:keys) AND collection = :collection',
        array(
          ':now' => REQUEST_TIME,
          ':keys' => $keys,
          ':collection' => $this->collection,
      ))->fetchAllAssoc('name');
      foreach ($keys as $key) {
        if (isset($result[$key])) {
          $values[$key] = unserialize($result[$key]->value);
        }
      }
    }
    catch (\Exception $e) {
      // @todo Perhaps if the database is never going to be available,
      //   key/value requests should return FALSE in order to allow exception
      //   handling to occur but for now, keep it an array, always.
    }
    return $values;
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreInterface::getAll().
   */
  public function getAll() {
    $result = $this->connection->query('SELECT name, value FROM {' . $this->connection->escapeTable($this->table) . '} WHERE collection = :collection AND expire > :now', array(':collection' => $this->collection, ':now' => REQUEST_TIME));
    $values = array();

    foreach ($result as $item) {
      if ($item) {
        $values[$item->name] = unserialize($item->value);
      }
    }
    return $values;
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreExpireInterface::setWithExpire().
   */
  function setWithExpire($key, $value, $expire) {
    $this->garbageCollection();
    $this->connection->merge($this->table)
      ->key(array(
        'name' => $key,
        'collection' => $this->collection,
      ))
      ->fields(array(
        'value' => serialize($value),
        'expire' => REQUEST_TIME + $expire,
      ))
      ->execute();
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreInterface::setWithExpireIfNotExists().
   */
  function setWithExpireIfNotExists($key, $value, $expire) {
    $this->garbageCollection();
    $result = $this->connection->merge($this->table)
      ->insertFields(array(
        'collection' => $this->collection,
        'name' => $key,
        'value' => serialize($value),
        'expire' => REQUEST_TIME + $expire,
      ))
      ->condition('collection', $this->collection)
      ->condition('name', $key)
      ->execute();
    return $result == Merge::STATUS_INSERT;
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreInterface::setMultipleWithExpire().
   */
  function setMultipleWithExpire(array $data, $expire) {
    foreach ($data as $key => $value) {
      $this->set($key, $value, $expire);
    }
  }

  /**
   * Implements Drupal\Core\KeyValueStore\KeyValueStoreInterface::deleteMultiple().
   */
  public function deleteMultiple(array $keys) {
    $this->garbageCollection();
    parent::deleteMultiple($keys);
  }

  /**
   * Deletes expired items.
   */
  protected function garbageCollection() {
    $this->connection->delete($this->table)
      ->condition('expire', REQUEST_TIME, '<')
      ->execute();
  }

}
