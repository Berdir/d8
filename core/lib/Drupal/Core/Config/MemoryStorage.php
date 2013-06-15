<?php

/**
 * @file
 * Contains \Drupal\Core\Config\MemoryStorage.
 */

namespace Drupal\Core\Config;

/**
 * Defines a config storage backend which uses just memory.
 */
class MemoryStorage implements StorageInterface {

  /**
   * An array containing all config objects.
   *
   * @var array
   */
  protected $storage;

  /**
   * Constructs a MemoryStorage object.
   *
   * @param array $array
   *   An array containing all config objects.
   */
  public function __construct(array &$array) {
    $this->storage = &$array;
  }

  /**
   * Throws an exception for the Config Storage Test.
   */
  protected function validateStorage() {
    if ($this->storage === array(NULL)) {
      throw new StorageException('Invalid storage specified.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    $this->validateStorage();
    return isset($this->storage[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    if (isset($this->storage[$name])) {
      if ($data = $this->decode($this->storage[$name])) {
        return $data;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $this->validateStorage();
    $this->storage[$name] = $this->encode($data);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    $this->validateStorage();
    if (isset($this->storage[$name])) {
      unset($this->storage[$name]);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
    $this->validateStorage();
    if (isset($this->storage[$name])) {
      $data = $this->storage[$name];
      $this->storage[$new_name] = $data;
      unset($this->storage[$name]);
      return TRUE;
    }
    else {
      throw new StorageException('Try to rename an non existing config.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    $this->validateStorage();
    return serialize($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    $this->validateStorage();
    $data = @unserialize($raw);
    return is_array($data) ? $data : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    $this->validateStorage();
    $names = array_keys($this->storage);
    return array_filter($names, function($name) use ($prefix) {
      if ($prefix != '') {
        return strpos($name, $prefix) === 0;
      }
      else {
        return TRUE;
      }
    });
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    $this->validateStorage();
    $this->storage = array();
    return TRUE;
  }

}
