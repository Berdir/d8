<?php

/**
 * @file
 * Definition of Drupal\entity\EntityProperty.
 */

namespace Drupal\entity;

/**
 * A list of PropertyContainer items.
 */
class EntityProperty implements EntityPropertyInterface {

  // Numerically indexed array of PropertyContainer objects
  protected $list;


  public function access($account) {
    // TODO: Implement access() method. Use item access.
  }

  public function validate() {
    // TODO: Implement validate() method.
  }

  public function offsetExists($offset) {
    // TODO: Implement offsetExists() method.
  }

  public function offsetSet($offset, $value) {
    // TODO: Implement offsetSet() method.
  }

  public function offsetUnset($offset) {
    // TODO: Implement offsetUnset() method.
  }

  public function offsetGet($offset) {
    return $this->list[$offset];
  }

  public function getIterator() {
    return new ArrayIterator($this->list);
  }

  public function count() {
    // TODO: Implement count() method.
  }

  /**
   * Delegate.
   */
  public function getProperties() {
    // TODO: Implement getProperties() method.
  }

  /**
   * Delegate.
   */
  public function getPropertyDefinitions() {
    // TODO: Implement getPropertyDefinitions() method.
  }

  /**
   * Delegate.
   */
  public function __get($name) {
    return $this[0][$name];
  }

  /**
   * Delegate.
   */
  public function get($name) {
    // TODO: Implement get() method.
  }

  /**
   * Delegate.
   */
  public function getRawValue($property_name) {
    // TODO: Implement getRawValue() method.
  }

  /**
   * Delegate.
   */
  public function set($name, $value) {
    // TODO: Implement set() method.
  }

  /**
   * Delegate.
   */
  public function __set($name, $value) {
    // TODO: Implement __set() method.
  }
}